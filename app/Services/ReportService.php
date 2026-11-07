<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function range(string $periodType, string $date): array
    {
        $date = Carbon::parse($date);

        return match ($periodType) {
            'weekly'  => [$date->copy()->startOfWeek(), $date->copy()->endOfWeek()],
            'monthly' => [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()],
            default   => [$date->copy()->startOfDay(), $date->copy()->endOfDay()],
        };
    }

    public function summary(Carbon $start, Carbon $end, array $filters = []): array
    {
        $sales = $this->salesQuery($start, $end, $filters)
            ->selectRaw('COALESCE(SUM(d.subtotal), 0) AS revenue')
            ->selectRaw('COALESCE(SUM(d.cost_price * d.quantity), 0) AS cost')
            ->selectRaw('COUNT(DISTINCT t.id_transaction) AS trx_count')
            ->first();

        $expense = (float) DB::table('expenses')
            ->where('id_business', $this->businessId())
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->sum('amount');

        $revenue = (float) $sales->revenue;
        $cost    = (float) $sales->cost;

        return [
            'revenue'   => $revenue,
            'cost'      => $cost,
            'expense'   => $expense,
            'profit'    => $revenue - $cost - $expense,
            'trx_count' => (int) $sales->trx_count,
        ];
    }

    public function dailyTrend(Carbon $start, Carbon $end, array $filters = []): array
    {
        $rows = $this->salesQuery($start, $end, $filters)
            ->selectRaw('DATE(t.transaction_date) AS day, SUM(d.subtotal) AS revenue')
            ->groupBy('day')
            ->pluck('revenue', 'day');

        $trend = [];

        foreach (CarbonPeriod::create($start->copy()->startOfDay(), $end->copy()->startOfDay()) as $day) {
            $trend[$day->toDateString()] = (float) ($rows[$day->toDateString()] ?? 0);
        }

        return $trend;
    }

    public function bestSelling(Carbon $start, Carbon $end, array $filters = [], int $limit = 5)
    {
        return $this->salesQuery($start, $end, $filters)
            ->selectRaw('d.item_name, SUM(d.quantity) AS total_sold, SUM(d.subtotal) AS total_revenue')
            ->groupBy('d.item_name')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get();
    }

    public function expenseByCategory(Carbon $start, Carbon $end)
    {
        return DB::table('expenses as e')
            ->join('expense_categories as c', 'c.id_expense_category', '=', 'e.id_expense_category')
            ->where('e.id_business', $this->businessId())
            ->whereBetween('e.expense_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('c.category_name, SUM(e.amount) AS total')
            ->groupBy('c.category_name')
            ->orderByDesc('total')
            ->get();
    }

    private function salesQuery(Carbon $start, Carbon $end, array $filters)
    {
        $query = DB::table('transaction_details as d')
            ->join('transactions as t', 't.id_transaction', '=', 'd.id_transaction')
            ->where('t.id_business', $this->businessId())
            ->where('t.status', 'completed')
            ->whereBetween('t.transaction_date', [$start->copy()->startOfDay(), $end->copy()->endOfDay()]);

        if (! empty($filters['payment_method'])) {
            $query->where('t.payment_method', $filters['payment_method']);
        }

        if (! empty($filters['id_category'])) {
            $query->join('items as i', 'i.id_item', '=', 'd.id_item')
                ->whereIn('i.id_category', (array) $filters['id_category']);
        }

        return $query;
    }

    private function businessId(): int
    {
        return (int) auth()->user()->id_business;
    }
}
