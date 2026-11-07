@extends('template.app')
@section('title', 'Target Omzet')
@section('subtitle', 'Tetapkan target dan pantau capaiannya dari penjualan nyata.')

@section('content')
  @php $periods = ['daily' => 'Harian', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan']; @endphp

  <div class="table-card-custom">
    <div class="table-header-control">
      <h5 class="card-title mb-0">Daftar Target</h5>
      <a href="{{ route('target.create') }}" class="btn-custom btn-custom-primary btn-custom-sm"><i class="bi bi-plus-lg"></i> Tetapkan Target</a>
    </div>

    <div class="table-responsive">
      <table class="table-custom">
        <thead><tr><th>Periode</th><th>Target</th><th>Tercapai</th><th style="min-width:180px">Progres</th><th class="text-center">Aksi</th></tr></thead>
        <tbody>
          @forelse ($targets as $target)
            <tr>
              <td>{{ $periods[$target->period_type] }}<br><small class="text-muted-green">{{ tanggal($target->start_date) }} - {{ tanggal($target->end_date) }}</small></td>
              <td class="table-amount">{{ rupiah($target->target_amount) }}</td>
              <td>{{ rupiah($target->achieved) }}</td>
              <td>
                <div class="progress" style="height:8px"><div class="progress-bar {{ $target->percent >= 100 ? 'bg-success' : 'bg-warning' }}" style="width: {{ min(100, $target->percent) }}%"></div></div>
                <small>{{ $target->percent }}%</small>
              </td>
              <td>
                <div class="d-flex justify-content-center gap-1">
                  <a href="{{ route('target.edit', $target) }}" class="table-btn-action" title="Ubah"><i class="bi bi-pencil"></i></a>
                  <x-delete-form :action="route('target.destroy', $target)" message="Hapus target ini?" />
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted-green py-4">Belum ada target omzet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <x-table-footer :paginator="$targets" />
  </div>
@endsection
