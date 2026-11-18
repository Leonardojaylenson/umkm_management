@extends('template.app')
@section('title', 'Target Omzet')
@section('subtitle', 'Tetapkan target dan pantau capaiannya dari penjualan nyata.')

@section('content')
  @php
    $periode = ['daily' => 'Harian', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan'];
  @endphp

  <div class="table-card-custom">
    <div class="table-header-control">
      <h5 class="card-title mb-0">Daftar Target</h5>
      <div class="d-flex gap-2">
        <a href="{{ route('laporan.index') }}" class="btn-custom btn-custom-light btn-custom-sm">Laporan</a>
        <a href="{{ route('target.create') }}" class="btn-custom btn-custom-primary btn-custom-sm"><i class="bi bi-plus-lg"></i> Tetapkan Target</a>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table-custom">
        <thead>
          <tr><th>Periode</th><th>Target</th><th>Tercapai</th><th style="min-width:180px">Progres</th><th class="text-center">Aksi</th></tr>
        </thead>
        <tbody>
          @forelse ($targets as $target)
            <tr>
              <td>
                {{ $periode[$target->period_type] }}<br>
                <small class="text-muted-green">{{ tanggal($target->start_date) }} - {{ tanggal($target->end_date) }}</small>
              </td>
              <td class="table-amount">{{ rupiah($target->target_amount) }}</td>
              <td>{{ rupiah($target->achieved) }}</td>
              <td>
                <div class="progress" style="height:8px">
                  <div class="progress-bar {{ $target->percent >= 100 ? 'bg-success' : 'bg-warning' }}" style="width: {{ min(100, $target->percent) }}%"></div>
                </div>
                <small>{{ $target->percent }}%</small>
              </td>
              <td>
                <div class="d-flex justify-content-center gap-1">
                  <a href="{{ route('target.edit', $target->id_revenue_target) }}" class="table-btn-action" title="Ubah"><i class="bi bi-pencil"></i></a>

                  <form action="{{ route('target.destroy', $target->id_revenue_target) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus target ini?')">
                    @csrf
                    @method('DELETE')
                    <button class="table-btn-action delete" title="Hapus"><i class="bi bi-trash"></i></button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted-green py-4">Belum ada target omzet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="table-footer-control">{{ $targets->links() }}</div>
  </div>
@endsection
