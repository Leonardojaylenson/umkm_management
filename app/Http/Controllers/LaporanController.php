<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Debt;
use App\Models\Receivable;
use App\Models\Report;
use App\Models\RevenueTarget;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function __construct(private ReportService $reports)
    {
    }

    public function dashboard()
    {
        $today      = now();
        $monthStart = $today->copy()->startOfMonth();

        $data = [
            'today'    => $this->reports->summary($today->copy()->startOfDay(), $today),
            'month'    => $this->reports->summary($monthStart, $today->copy()->endOfMonth()),
            'trend'    => $this->reports->dailyTrend($today->copy()->subDays(29), $today),
            'best'     => $this->reports->bestSelling($monthStart, $today->copy()->endOfMonth()),
            'lowStock' => DB::table('v_low_stock_items')->where('id_business', $this->businessId())->orderBy('stock')->limit(6)->get(),
        ];

        if (auth()->user()->isOwner()) {
            $data += $this->ownerDashboardData($today);
        }

        return view('laporan.dashboard', $data);
    }

    private function ownerDashboardData($today): array
    {
        $target = RevenueTarget::whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->latest('start_date')->first();

        $targetProgress = null;
        if ($target) {
            $achieved = $this->reports->summary($target->start_date, $target->end_date)['revenue'];
            $targetProgress = [
                'target'   => $target,
                'achieved' => $achieved,
                'percent'  => min(100, round($achieved / max(1, (float) $target->target_amount) * 100)),
            ];
        }

        $limit   = $today->copy()->addDays(7)->toDateString();
        $dueSoon = fn ($model) => $model::where('status', '!=', 'paid')
            ->whereNotNull('due_date')->where('due_date', '<=', $limit)
            ->selectRaw('COUNT(*) AS n, COALESCE(SUM(amount - paid_amount), 0) AS total')->first();

        return [
            'targetProgress' => $targetProgress,
            'debtDue'        => $dueSoon(Debt::class),
            'receivableDue'  => $dueSoon(Receivable::class),
        ];
    }

    public function reportIndex()
    {
        $reports = Report::latest('created_at')->latest('id_report')->paginate(10);

        return view('laporan.laporan', compact('reports'));
    }

    public function reportCreate()
    {
        return view('laporan.laporan-form', $this->reportFormData(new Report([
            'period_type' => 'monthly',
            'start_date'  => now()->startOfMonth(),
        ])));
    }

    public function reportStore(Request $request)
    {
        $report = Report::create($this->buildReportPayload($request) + ['id_user' => auth()->id()]);

        return redirect()->route('laporan.show', $report)->with('success', 'Laporan berhasil dibuat.');
    }

    public function reportShow(Report $report)
    {
        $filters = $report->filters ?? [];

        return view('laporan.laporan-detail', [
            'report'     => $report,
            'filterText' => $this->describeFilters($filters),
            'trend'      => $this->reports->dailyTrend($report->start_date, $report->end_date, $filters),
            'best'       => $this->reports->bestSelling($report->start_date, $report->end_date, $filters, 10),
            'expenses'   => $this->reports->expenseByCategory($report->start_date, $report->end_date),
        ]);
    }

    public function reportEdit(Report $report)
    {
        return view('laporan.laporan-form', $this->reportFormData($report));
    }

    public function reportUpdate(Request $request, Report $report)
    {
        $report->update($this->buildReportPayload($request));

        return redirect()->route('laporan.show', $report)->with('success', 'Laporan diperbarui dan dihitung ulang.');
    }

    public function reportDestroy(Report $report)
    {
        $report->delete();

        return redirect()->route('laporan.index')->with('success', 'Laporan berhasil dihapus.');
    }

    private function buildReportPayload(Request $request): array
    {
        $data = $request->validate([
            'report_name'    => 'required|string|max:150',
            'period_type'    => 'required|in:daily,weekly,monthly',
            'reference_date' => 'required|date',
            'id_category'    => 'nullable|array',
            'id_category.*'  => $this->existsInBusiness('categories', 'id_category'),
            'payment_method' => 'nullable|in:cash,transfer,qris,e-wallet',
        ]);

        [$start, $end] = $this->reports->range($data['period_type'], $data['reference_date']);

        $filters = array_filter([
            'id_category'    => array_map('intval', $data['id_category'] ?? []),
            'payment_method' => $data['payment_method'] ?? null,
        ]);

        $summary = $this->reports->summary($start, $end, $filters);

        return [
            'report_name'   => $data['report_name'],
            'period_type'   => $data['period_type'],
            'start_date'    => $start->toDateString(),
            'end_date'      => $end->toDateString(),
            'filters'       => $filters ?: null,
            'total_revenue' => $summary['revenue'],
            'total_cost'    => $summary['cost'],
            'total_expense' => $summary['expense'],
            'net_profit'    => $summary['profit'],
        ];
    }

    private function reportFormData(Report $report): array
    {
        return ['report' => $report, 'categories' => Category::orderBy('category_name')->get()];
    }

    private function describeFilters(array $filters): string
    {
        $parts = [];

        if (! empty($filters['id_category'])) {
            $parts[] = 'Kategori: ' . Category::whereIn('id_category', $filters['id_category'])->pluck('category_name')->implode(', ');
        }

        if (! empty($filters['payment_method'])) {
            $parts[] = 'Metode bayar: ' . strtoupper($filters['payment_method']);
        }

        return $parts ? implode(' | ', $parts) : 'Tanpa filter';
    }

    public function targetIndex()
    {
        $targets = RevenueTarget::latest('start_date')->paginate(10);

        $targets->getCollection()->transform(function ($target) {
            $target->achieved = $this->reports->summary($target->start_date, $target->end_date)['revenue'];
            $target->percent  = round($target->achieved / max(1, (float) $target->target_amount) * 100);

            return $target;
        });

        return view('laporan.target', compact('targets'));
    }

    public function targetCreate()
    {
        return view('laporan.target-form', ['target' => new RevenueTarget([
            'period_type' => 'monthly',
            'start_date'  => now()->startOfMonth(),
            'end_date'    => now()->endOfMonth(),
        ])]);
    }

    public function targetStore(Request $request)
    {
        RevenueTarget::create($this->validateTarget($request));

        return redirect()->route('target.index')->with('success', 'Target omzet berhasil ditetapkan.');
    }

    public function targetEdit(RevenueTarget $target)
    {
        return view('laporan.target-form', compact('target'));
    }

    public function targetUpdate(Request $request, RevenueTarget $target)
    {
        $target->update($this->validateTarget($request));

        return redirect()->route('target.index')->with('success', 'Target omzet berhasil diperbarui.');
    }

    public function targetDestroy(RevenueTarget $target)
    {
        $target->delete();

        return redirect()->route('target.index')->with('success', 'Target omzet berhasil dihapus.');
    }

    private function validateTarget(Request $request): array
    {
        return $request->validate([
            'period_type'   => 'required|in:daily,weekly,monthly',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'target_amount' => 'required|numeric|min:1',
        ]);
    }
}
