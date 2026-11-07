<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
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

        return view('laporan.dashboard', [
            'today'    => $this->reports->summary($today->copy()->startOfDay(), $today),
            'month'    => $this->reports->summary($monthStart, $today->copy()->endOfMonth()),
            'trend'    => $this->reports->dailyTrend($today->copy()->subDays(29), $today),
            'best'     => $this->reports->bestSelling($monthStart, $today->copy()->endOfMonth()),
            'lowStock' => DB::table('v_low_stock_items')->where('id_business', $this->businessId())->orderBy('stock')->limit(6)->get(),
        ]);
    }
}
