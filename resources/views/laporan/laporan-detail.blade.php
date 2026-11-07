@extends('template.app')
@section('title', 'Detail Laporan')
@section('subtitle', $report->report_name)

@section('content')
  @php $periods = ['daily' => 'Harian', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan']; @endphp

  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div class="text-muted-green">
      {{ $periods[$report->period_type] }} &middot; {{ tanggal($report->start_date) }} - {{ tanggal($report->end_date) }}
      &middot; {{ $filterText }}
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('laporan.edit', $report) }}" class="btn-custom btn-custom-outline-primary btn-custom-sm">Ubah / Hitung Ulang</a>
      <button onclick="window.print()" class="btn-custom btn-custom-light btn-custom-sm"><i class="bi bi-printer"></i> Cetak</button>
      <a href="{{ route('laporan.index') }}" class="btn-custom btn-custom-light btn-custom-sm">Kembali</a>
    </div>
  </div>

    <div class="row g-4 mb-4">
    <div class="col-6 col-xl-3"><x-stat-card label="Omzet" :value="rupiah($report->total_revenue)" icon="bi-cash-coin" /></div>
    <div class="col-6 col-xl-3"><x-stat-card label="Total Modal" :value="rupiah($report->total_cost)" icon="bi-box-seam" /></div>
    <div class="col-6 col-xl-3"><x-stat-card label="Pengeluaran" :value="rupiah($report->total_expense)" icon="bi-wallet2" /></div>
    <div class="col-6 col-xl-3"><x-stat-card label="Laba Bersih" :value="rupiah($report->net_profit)" :sub="$report->net_profit < 0 ? 'Rugi' : 'Untung'" icon="bi-piggy-bank" /></div>
  </div>

  <div class="card mb-4">
    <div class="card-header"><h2 class="card-title">Tren Omzet</h2></div>
    <div id="report-trend-chart"></div>
  </div>

  <div class="row g-4">
    <div class="col-12 col-lg-7">
      <div class="table-card-custom h-100">
        <div class="table-header-control"><h5 class="card-title mb-0">Item Terlaris</h5></div>
        <div class="table-responsive">
          <table class="table-custom">
            <thead><tr><th>#</th><th>Item</th><th>Terjual</th><th>Omzet</th></tr></thead>
            <tbody>
              @forelse ($best as $i => $row)
                <tr><td>{{ $i + 1 }}</td><td class="table-product-name">{{ $row->item_name }}</td><td>{{ qty($row->total_sold) }}</td><td class="table-amount">{{ rupiah($row->total_revenue) }}</td></tr>
              @empty
                <tr><td colspan="4" class="text-center text-muted-green py-4">Tidak ada penjualan pada periode ini.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-5">
      <div class="table-card-custom h-100">
        <div class="table-header-control"><h5 class="card-title mb-0">Pengeluaran per Kategori</h5></div>
        <div class="table-responsive">
          <table class="table-custom">
            <thead><tr><th>Kategori</th><th class="text-end">Total</th></tr></thead>
            <tbody>
              @forelse ($expenses as $row)
                <tr><td>{{ $row->category_name }}</td><td class="text-end table-amount">{{ rupiah($row->total) }}</td></tr>
              @empty
                <tr><td colspan="2" class="text-center text-muted-green py-4">Tidak ada pengeluaran.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  new ApexCharts(document.querySelector('#report-trend-chart'), {
    chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans, sans-serif' },
    series: [{ name: 'Omzet', data: @json(array_values($trend)) }],
    xaxis: { categories: @json(array_map(fn ($d) => \Carbon\Carbon::parse($d)->format('d M'), array_keys($trend))), labels: { style: { colors: '#6C7E75' } } },
    yaxis: { labels: { formatter: v => 'Rp ' + Math.round(v).toLocaleString('id-ID'), style: { colors: '#6C7E75' } } },
    colors: ['#072F1F'],
    plotOptions: { bar: { columnWidth: '50%' } },
    dataLabels: { enabled: false },
    grid: { borderColor: '#E9EFEF', strokeDashArray: 4 },
    tooltip: { y: { formatter: v => 'Rp ' + Math.round(v).toLocaleString('id-ID') } },
  }).render();
</script>
@endpush
