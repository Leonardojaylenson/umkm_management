@extends('template.app')
@section('title', 'Laporan')
@section('subtitle', 'Laporan tersimpan: omzet, modal, pengeluaran, dan laba per periode.')

@section('content')
  @php $periods = ['daily' => 'Harian', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan']; @endphp

  <div class="table-card-custom">
    <div class="table-header-control">
      <h5 class="card-title mb-0">Laporan Tersimpan</h5>
      <a href="{{ route('laporan.create') }}" class="btn-custom btn-custom-primary btn-custom-sm"><i class="bi bi-plus-lg"></i> Buat Laporan</a>
    </div>

    <div class="table-responsive">
      <table class="table-custom">
        <thead><tr><th>Nama</th><th>Periode</th><th>Omzet</th><th>Pengeluaran</th><th>Laba Bersih</th><th class="text-center">Aksi</th></tr></thead>
        <tbody>
          @forelse ($reports as $report)
            <tr>
              <td class="table-product-name">{{ $report->report_name }}</td>
              <td>{{ $periods[$report->period_type] }}<br><small class="text-muted-green">{{ tanggal($report->start_date) }} - {{ tanggal($report->end_date) }}</small></td>
              <td>{{ rupiah($report->total_revenue) }}</td>
              <td>{{ rupiah($report->total_expense) }}</td>
              <td class="table-amount {{ $report->net_profit < 0 ? 'text-danger' : '' }}">{{ rupiah($report->net_profit) }}</td>
              <td>
                <div class="d-flex justify-content-center gap-1">
                  <a href="{{ route('laporan.show', $report) }}" class="table-btn-action" title="Lihat"><i class="bi bi-eye"></i></a>
                  <a href="{{ route('laporan.edit', $report) }}" class="table-btn-action" title="Ubah filter / hitung ulang"><i class="bi bi-pencil"></i></a>
                  <x-delete-form :action="route('laporan.destroy', $report)" :message="'Hapus laporan ' . $report->report_name . '?'" />
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted-green py-4">Belum ada laporan tersimpan.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <x-table-footer :paginator="$reports" />
  </div>
@endsection
