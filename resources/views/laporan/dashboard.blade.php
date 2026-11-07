@extends('template.app')
@section('title', 'Dashboard')
@section('subtitle', 'Ringkasan omzet, laba rugi, dan item terlaris.')

@section('content')
  @php $isOwner = auth()->user()->isOwner(); @endphp

  <div class="row g-4 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
      <x-stat-card label="Omzet Hari Ini" :value="rupiah($today['revenue'])" :sub="$today['trx_count'] . ' transaksi'" icon="bi-cash-coin" />
    </div>
    @if ($isOwner)
      <div class="col-12 col-sm-6 col-xl-3">
        <x-stat-card label="Omzet Bulan Ini" :value="rupiah($month['revenue'])" :sub="$month['trx_count'] . ' transaksi'" icon="bi-graph-up-arrow" />
      </div>
      <div class="col-12 col-sm-6 col-xl-3">
        <x-stat-card label="Pengeluaran Bulan Ini" :value="rupiah($month['expense'])" sub="Bahan, gaji, sewa, dll" icon="bi-wallet2" />
      </div>
      <div class="col-12 col-sm-6 col-xl-3">
        <x-stat-card label="Laba Bersih Bulan Ini" :value="rupiah($month['profit'])" sub="Omzet - modal - pengeluaran" icon="bi-piggy-bank" />
      </div>
    @endif
  </div>

  <div class="card mb-4">
    <div class="card-header"><h2 class="card-title">Tren Penjualan 30 Hari Terakhir</h2></div>
    <div id="sales-trend-chart"></div>
  </div>

  <div class="row g-4">
    <div class="col-12 col-lg-6">
      <div class="table-card-custom h-100">
        <div class="table-header-control"><h5 class="card-title mb-0">Item Terlaris Bulan Ini</h5></div>
        <div class="table-responsive">
          <table class="table-custom">
            <thead><tr><th>#</th><th>Item</th><th>Terjual</th><th>Omzet</th></tr></thead>
            <tbody>
              @forelse ($best as $i => $row)
                <tr><td>{{ $i + 1 }}</td><td class="table-product-name">{{ $row->item_name }}</td><td>{{ qty($row->total_sold) }}</td><td class="table-amount">{{ rupiah($row->total_revenue) }}</td></tr>
              @empty
                <tr><td colspan="4" class="text-center text-muted-green py-4">Belum ada penjualan bulan ini.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-6">
      <div class="table-card-custom h-100">
        <div class="table-header-control"><h5 class="card-title mb-0">Peringatan Stok Menipis</h5></div>
        <div class="table-responsive">
          <table class="table-custom">
            <thead><tr><th>Item</th><th>Stok</th><th>Batas</th></tr></thead>
            <tbody>
              @forelse ($lowStock as $row)
                <tr>
                  <td class="table-product-name">{{ $row->item_name }}</td>
                  <td><span class="badge-table pending">{{ qty($row->stock) }} {{ $row->unit_name }}</span></td>
                  <td>{{ qty($row->min_stock) }}</td>
                </tr>
              @empty
                <tr><td colspan="3" class="text-center text-muted-green py-4">Semua stok aman.</td></tr>
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
  new ApexCharts(document.querySelector('#sales-trend-chart'), {
    chart: { type: 'area', height: 300, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans, sans-serif' },
    series: [{ name: 'Omzet', data: @json(array_values($trend)) }],
    xaxis: { categories: @json(array_map(fn ($d) => \Carbon\Carbon::parse($d)->format('d M'), array_keys($trend))), tickAmount: 8, labels: { style: { colors: '#6C7E75' } } },
    yaxis: { labels: { formatter: v => 'Rp ' + Math.round(v).toLocaleString('id-ID'), style: { colors: '#6C7E75' } } },
    colors: ['#072F1F'],
    stroke: { width: 2, curve: 'smooth' },
    fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.02 } },
    dataLabels: { enabled: false },
    grid: { borderColor: '#E9EFEF', strokeDashArray: 4 },
    tooltip: { y: { formatter: v => 'Rp ' + Math.round(v).toLocaleString('id-ID') } },
  }).render();
</script>
@endpush
