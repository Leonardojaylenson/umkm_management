<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Debt;
use App\Models\Receivable;
use App\Models\Report;
use App\Models\RevenueTarget;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function dashboard()
    {
        $today      = now();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd   = $today->copy()->endOfMonth();

        $data = [
            'today'    => $this->ringkasan($today->copy()->startOfDay(), $today),
            'month'    => $this->ringkasan($monthStart, $monthEnd),
            'trend'    => $this->trenHarian($today->copy()->subDays(29), $today),
            'best'     => $this->itemTerlaris($monthStart, $monthEnd, [], 5),
            'lowStock' => DB::table('v_low_stock_items')->where('id_business', $this->businessId())->orderBy('stock')->limit(6)->get(),
        ];

        if (auth()->user()->canAccess('laporan')) {
            $data['targetProgress'] = $this->progresTarget($today);
        }

        if (auth()->user()->canAccess('hutang')) {
            $batas = $today->copy()->addDays(7)->toDateString();

            $data['debtDue'] = Debt::where('id_business', $this->businessId())
                ->where('status', '!=', 'paid')->whereNotNull('due_date')->where('due_date', '<=', $batas)
                ->selectRaw('COUNT(*) AS n, COALESCE(SUM(amount - paid_amount), 0) AS total')->first();

            $data['receivableDue'] = Receivable::where('id_business', $this->businessId())
                ->where('status', '!=', 'paid')->whereNotNull('due_date')->where('due_date', '<=', $batas)
                ->selectRaw('COUNT(*) AS n, COALESCE(SUM(amount - paid_amount), 0) AS total')->first();
        }

        return view('laporan.dashboard', $data);
    }

    private function progresTarget($today)
    {
        $target = RevenueTarget::where('id_business', $this->businessId())
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->latest('start_date')->first();

        if (! $target) {
            return null;
        }

        $tercapai = $this->ringkasan($target->start_date, $target->end_date)['revenue'];
        $persen   = min(100, round($tercapai / max(1, $target->target_amount) * 100));

        return ['target' => $target, 'achieved' => $tercapai, 'percent' => $persen];
    }

    public function reportIndex()
    {
        $reports = Report::where('id_business', $this->businessId())
            ->latest('created_at')->latest('id_report')->paginate(10);

        return view('laporan.laporan', compact('reports'));
    }

    public function reportCreate()
    {
        $report     = new Report(['period_type' => 'monthly', 'start_date' => now()->startOfMonth()]);
        $categories = Category::where('id_business', $this->businessId())->orderBy('category_name')->get();

        return view('laporan.laporan-form', compact('report', 'categories'));
    }

    public function reportStore(Request $request)
    {
        $data = $this->dataLaporan($request);

        $data['id_business'] = $this->businessId();
        $data['id_user']     = auth()->id();

        $report = Report::create($data);

        return redirect()->route('laporan.show', $report->id_report)->with('success', 'Laporan berhasil dibuat.');
    }

    public function reportShow($id)
    {
        $report  = Report::where('id_business', $this->businessId())->findOrFail($id);
        $filters = $report->filters ?? [];

        $trend    = $this->trenHarian($report->start_date, $report->end_date, $filters);
        $best     = $this->itemTerlaris($report->start_date, $report->end_date, $filters, 10);
        $expenses = $this->pengeluaranPerKategori($report->start_date, $report->end_date);

        $teksFilter = [];
        if (! empty($filters['id_category'])) {
            $nama = Category::whereIn('id_category', $filters['id_category'])->pluck('category_name')->implode(', ');
            $teksFilter[] = 'Kategori: ' . $nama;
        }
        if (! empty($filters['payment_method'])) {
            $teksFilter[] = 'Metode bayar: ' . strtoupper($filters['payment_method']);
        }
        $filterText = count($teksFilter) > 0 ? implode(' | ', $teksFilter) : 'Tanpa filter';

        return view('laporan.laporan-detail', compact('report', 'filterText', 'trend', 'best', 'expenses'));
    }

    public function reportEdit($id)
    {
        $bid        = $this->businessId();
        $report     = Report::where('id_business', $bid)->findOrFail($id);
        $categories = Category::where('id_business', $bid)->orderBy('category_name')->get();

        return view('laporan.laporan-form', compact('report', 'categories'));
    }

    public function reportUpdate(Request $request, $id)
    {
        $report = Report::where('id_business', $this->businessId())->findOrFail($id);

        $report->update($this->dataLaporan($request));

        return redirect()->route('laporan.show', $report->id_report)->with('success', 'Laporan diperbarui dan dihitung ulang.');
    }

    public function reportDestroy($id)
    {
        $report = Report::where('id_business', $this->businessId())->findOrFail($id);
        $report->delete();

        return redirect()->route('laporan.index')->with('success', 'Laporan berhasil dihapus.');
    }

    private function dataLaporan(Request $request)
    {
        $request->validate([
            'report_name'    => 'required|max:150',
            'period_type'    => 'required|in:daily,weekly,monthly',
            'reference_date' => 'required|date',
            'id_category'    => 'nullable|array',
            'id_category.*'  => 'exists:categories,id_category,id_business,' . $this->businessId(),
            'payment_method' => 'nullable|in:cash,transfer,qris,e-wallet',
        ]);

        $tanggal = Carbon::parse($request->reference_date);

        if ($request->period_type == 'weekly') {
            $mulai   = $tanggal->copy()->startOfWeek();
            $selesai = $tanggal->copy()->endOfWeek();
        } elseif ($request->period_type == 'monthly') {
            $mulai   = $tanggal->copy()->startOfMonth();
            $selesai = $tanggal->copy()->endOfMonth();
        } else {
            $mulai   = $tanggal->copy()->startOfDay();
            $selesai = $tanggal->copy()->endOfDay();
        }

        $filters = [];
        if ($request->id_category) {
            $filters['id_category'] = array_map('intval', $request->id_category);
        }
        if ($request->payment_method) {
            $filters['payment_method'] = $request->payment_method;
        }

        $hasil = $this->ringkasan($mulai, $selesai, $filters);

        return [
            'report_name'   => $request->report_name,
            'period_type'   => $request->period_type,
            'start_date'    => $mulai->toDateString(),
            'end_date'      => $selesai->toDateString(),
            'filters'       => count($filters) > 0 ? $filters : null,
            'total_revenue' => $hasil['revenue'],
            'total_cost'    => $hasil['cost'],
            'total_expense' => $hasil['expense'],
            'net_profit'    => $hasil['profit'],
        ];
    }

    public function targetIndex()
    {
        $targets = RevenueTarget::where('id_business', $this->businessId())->latest('start_date')->paginate(10);

        foreach ($targets as $target) {
            $target->achieved = $this->ringkasan($target->start_date, $target->end_date)['revenue'];
            $target->percent  = round($target->achieved / max(1, $target->target_amount) * 100);
        }

        return view('laporan.target', compact('targets'));
    }

    public function targetCreate()
    {
        $target = new RevenueTarget([
            'period_type' => 'monthly',
            'start_date'  => now()->startOfMonth(),
            'end_date'    => now()->endOfMonth(),
        ]);

        return view('laporan.target-form', compact('target'));
    }

    public function targetStore(Request $request)
    {
        $request->validate([
            'period_type'   => 'required|in:daily,weekly,monthly',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'target_amount' => 'required|numeric|min:1',
        ]);

        RevenueTarget::create([
            'id_business'   => $this->businessId(),
            'period_type'   => $request->period_type,
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date,
            'target_amount' => $request->target_amount,
        ]);

        return redirect()->route('target.index')->with('success', 'Target omzet berhasil ditetapkan.');
    }

    public function targetEdit($id)
    {
        $target = RevenueTarget::where('id_business', $this->businessId())->findOrFail($id);

        return view('laporan.target-form', compact('target'));
    }

    public function targetUpdate(Request $request, $id)
    {
        $target = RevenueTarget::where('id_business', $this->businessId())->findOrFail($id);

        $request->validate([
            'period_type'   => 'required|in:daily,weekly,monthly',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'target_amount' => 'required|numeric|min:1',
        ]);

        $target->update([
            'period_type'   => $request->period_type,
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date,
            'target_amount' => $request->target_amount,
        ]);

        return redirect()->route('target.index')->with('success', 'Target omzet berhasil diperbarui.');
    }

    public function targetDestroy($id)
    {
        $target = RevenueTarget::where('id_business', $this->businessId())->findOrFail($id);
        $target->delete();

        return redirect()->route('target.index')->with('success', 'Target omzet berhasil dihapus.');
    }

    private function queryPenjualan($mulai, $selesai, $filters)
    {
        $query = DB::table('transaction_details as d')
            ->join('transactions as t', 't.id_transaction', '=', 'd.id_transaction')
            ->where('t.id_business', $this->businessId())
            ->where('t.status', 'completed')
            ->whereBetween('t.transaction_date', [$mulai->copy()->startOfDay(), $selesai->copy()->endOfDay()]);

        if (! empty($filters['payment_method'])) {
            $query->where('t.payment_method', $filters['payment_method']);
        }

        if (! empty($filters['id_category'])) {
            $query->join('items as i', 'i.id_item', '=', 'd.id_item')
                  ->whereIn('i.id_category', $filters['id_category']);
        }

        return $query;
    }

    private function ringkasan($mulai, $selesai, $filters = [])
    {
        $penjualan = $this->queryPenjualan($mulai, $selesai, $filters)
            ->selectRaw('COALESCE(SUM(d.subtotal), 0) AS revenue')
            ->selectRaw('COALESCE(SUM(d.cost_price * d.quantity), 0) AS cost')
            ->selectRaw('COUNT(DISTINCT t.id_transaction) AS trx_count')
            ->first();

        $pengeluaran = DB::table('expenses')
            ->where('id_business', $this->businessId())
            ->whereBetween('expense_date', [$mulai->toDateString(), $selesai->toDateString()])
            ->sum('amount');

        return [
            'revenue'   => (float) $penjualan->revenue,
            'cost'      => (float) $penjualan->cost,
            'expense'   => (float) $pengeluaran,
            'profit'    => $penjualan->revenue - $penjualan->cost - $pengeluaran,
            'trx_count' => (int) $penjualan->trx_count,
        ];
    }

    private function trenHarian($mulai, $selesai, $filters = [])
    {
        $data = $this->queryPenjualan($mulai, $selesai, $filters)
            ->selectRaw('DATE(t.transaction_date) AS day, SUM(d.subtotal) AS revenue')
            ->groupBy('day')
            ->pluck('revenue', 'day');

        $tren = [];

        foreach (CarbonPeriod::create($mulai->copy()->startOfDay(), $selesai->copy()->startOfDay()) as $hari) {
            $tren[$hari->toDateString()] = (float) ($data[$hari->toDateString()] ?? 0);
        }

        return $tren;
    }

    private function itemTerlaris($mulai, $selesai, $filters, $batas)
    {
        return $this->queryPenjualan($mulai, $selesai, $filters)
            ->selectRaw('d.item_name, SUM(d.quantity) AS total_sold, SUM(d.subtotal) AS total_revenue')
            ->groupBy('d.item_name')
            ->orderByDesc('total_sold')
            ->limit($batas)
            ->get();
    }

    private function pengeluaranPerKategori($mulai, $selesai)
    {
        return DB::table('expenses as e')
            ->join('expense_categories as c', 'c.id_expense_category', '=', 'e.id_expense_category')
            ->where('e.id_business', $this->businessId())
            ->whereBetween('e.expense_date', [$mulai->toDateString(), $selesai->toDateString()])
            ->selectRaw('c.category_name, SUM(e.amount) AS total')
            ->groupBy('c.category_name')
            ->orderByDesc('total')
            ->get();
    }
}
