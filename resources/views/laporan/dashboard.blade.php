@extends('template.app')
@section('title', 'Dashboard')
@section('subtitle', 'Ringkasan omzet, laba rugi, dan item terlaris.')

@section('content')
  @php
    $lihatKeuangan = auth()->user()->canAccess('laporan');
    $lihatHutang   = auth()->user()->canAccess('hutang');
  @endphp

  <div class="row g-4 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="card card-stat h-100">
        <div class="card-header">
          <span class="stat-label">Omzet Hari Ini</span>
          <i class="bi bi-cash-coin text-muted-green fs-5"></i>
        </div>
        <div class="stat-value">{{ rupiah($today['revenue']) }}</div>
        <div class="text-muted-green small mt-1">{{ $today['trx_count'] }} transaksi</div>
      </div>
    </div>

    @if ($lihatKeuangan)
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-stat h-100">
          <div class="card-header">
            <span class="stat-label">Omzet Bulan Ini</span>
            <i class="bi bi-graph-up-arrow text-muted-green fs-5"></i>
          </div>
          <div class="stat-value">{{ rupiah($month['revenue']) }}</div>
          <div class="text-muted-green small mt-1">{{ $month['trx_count'] }} transaksi</div>
        </div>
      </div>

      <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-stat h-100">
          <div class="card-header">
            <span class="stat-label">Pengeluaran Bulan Ini</span>
            <i class="bi bi-wallet2 text-muted-green fs-5"></i>
          </div>
          <div class="stat-value">{{ rupiah($month['expense']) }}</div>
          <div class="text-muted-green small mt-1">Bahan, gaji, sewa, dll</div>
        </div>
      </div>

      <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-stat h-100">
          <div class="card-header">
            <span class="stat-label">Laba Bersih Bulan Ini</span>
            <i class="bi bi-piggy-bank text-muted-green fs-5"></i>
          </div>
          <div class="stat-value">{{ rupiah($month['profit']) }}</div>
          <div class="text-muted-green small mt-1">Omzet - modal - pengeluaran</div>
        </div>
      </div>
    @endif
  </div>

  <div class="row g-4 mb-4">
    <div class="col-12 col-xl-8">
      <div class="card h-100">
        <div class="card-header"><h2 class="card-title">Tren Penjualan 30 Hari Terakhir</h2></div>
        <div id="sales-trend-chart"></div>
      </div>
    </div>

    <div class="col-12 col-xl-4">
      @if ($lihatKeuangan)
        <div class="card mb-4">
          <div class="card-header"><h2 class="card-title">Target Omzet</h2></div>

          @if ($targetProgress)
            <div class="stat-value">{{ $targetProgress['percent'] }}%</div>
            <div class="progress my-2" style="height:10px">
              <div class="progress-bar bg-success" style="width: {{ $targetProgress['percent'] }}%"></div>
            </div>
            <div class="small text-muted-green">{{ rupiah($targetProgress['achieved']) }} dari {{ rupiah($targetProgress['target']->target_amount) }}</div>
            <div class="small text-muted-green">{{ tanggal($targetProgress['target']->start_date) }} - {{ tanggal($targetProgress['target']->end_date) }}</div>
          @else
            <p class="text-muted-green mb-2">Belum ada target untuk hari ini.</p>
            <a href="{{ route('target.create') }}" class="btn-custom btn-custom-outline-primary btn-custom-sm align-self-start">Tetapkan Target</a>
          @endif
        </div>
      @endif

      @if ($lihatHutang)
        <div class="card">
          <div class="card-header"><h2 class="card-title">Jatuh Tempo &le; 7 Hari</h2></div>
          <div class="d-flex justify-content-between mb-2">
            <a href="{{ route('hutang.index') }}">Hutang ({{ $debtDue->n }})</a>
            <strong>{{ rupiah($debtDue->total) }}</strong>
          </div>
          <div class="d-flex justify-content-between">
            <a href="{{ route('piutang.index') }}">Piutang ({{ $receivableDue->n }})</a>
            <strong>{{ rupiah($receivableDue->total) }}</strong>
          </div>
        </div>
      @endif
    </div>
  </div>

  <div class="row g-4">
    <div class="col-12 col-lg-6">
      <div class="table-card-custom h-100">
        <div class="table-header-control"><h5 class="card-title mb-0">Item Terlaris Bulan Ini</h5></div>
        <div class="table-responsive">
          <table class="table-custom">
            <thead>
              <tr><th>#</th><th>Item</th><th>Terjual</th><th>Omzet</th></tr>
            </thead>
            <tbody>
              @forelse ($best as $i => $row)
                <tr>
                  <td>{{ $i + 1 }}</td>
                  <td class="table-product-name">{{ $row->item_name }}</td>
                  <td>{{ qty($row->total_sold) }}</td>
                  <td class="table-amount">{{ rupiah($row->total_revenue) }}</td>
                </tr>
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
        <div class="table-header-control">
          <h5 class="card-title mb-0">Peringatan Stok Menipis</h5>
          @if (auth()->user()->canAccess('item'))
            <a href="{{ route('item.index', ['filter' => 'stok-menipis']) }}" class="small">Lihat semua</a>
          @endif
        </div>
        <div class="table-responsive">
          <table class="table-custom">
            <thead>
              <tr><th>Item</th><th>Stok</th><th>Batas</th></tr>
            </thead>
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

@php
  $labelTanggal = [];
  foreach (array_keys($trend) as $tgl) {
    $labelTanggal[] = date('d M', strtotime($tgl));
  }
@endphp

@push('scripts')
<script>
  var chart = new ApexCharts(document.querySelector('#sales-trend-chart'), {
    chart: { type: 'area', height: 300, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans, sans-serif' },
    series: [{ name: 'Omzet', data: @json(array_values($trend)) }],
    xaxis: { categories: @json($labelTanggal), tickAmount: 8, labels: { style: { colors: '#6C7E75' } } },
    yaxis: { labels: { formatter: function (v) { return 'Rp ' + Math.round(v).toLocaleString('id-ID'); }, style: { colors: '#6C7E75' } } },
    colors: ['#072F1F'],
    stroke: { width: 2, curve: 'smooth' },
    fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.02 } },
    dataLabels: { enabled: false },
    grid: { borderColor: '#E9EFEF', strokeDashArray: 4 },
    tooltip: { y: { formatter: function (v) { return 'Rp ' + Math.round(v).toLocaleString('id-ID'); } } }
  });

  chart.render();
</script>
@endpush
