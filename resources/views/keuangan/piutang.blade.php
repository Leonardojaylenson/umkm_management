@extends('template.app')
@section('title', 'Piutang Pelanggan')
@section('subtitle', 'Tagihan pelanggan yang belum dibayar (otomatis dari transaksi belum lunas / DP).')

@section('content')
  <div class="row g-4 mb-4">
    <div class="col-12 col-md-4"><x-stat-card label="Total Piutang Belum Lunas" :value="rupiah($outstanding)" icon="bi-cash-stack" /></div>
  </div>

  <div class="table-card-custom">
    <form class="table-header-control" method="GET">
      <div class="table-filter-group">
        <select name="status" class="form-select-custom form-control-custom-sm" onchange="this.form.submit()">
          <option value="">Semua Status</option>
          <option value="unpaid" @selected(request('status') === 'unpaid')>Belum Lunas</option>
          <option value="partial" @selected(request('status') === 'partial')>Dicicil</option>
          <option value="paid" @selected(request('status') === 'paid')>Lunas</option>
        </select>
      </div>
      <a href="{{ route('piutang.create') }}" class="btn-custom btn-custom-primary btn-custom-sm"><i class="bi bi-plus-lg"></i> Catat Piutang</a>
    </form>

    <div class="table-responsive">
      <table class="table-custom">
        <thead><tr><th>Pelanggan</th><th>Catatan</th><th>Total</th><th>Sisa</th><th>Jatuh Tempo</th><th>Status</th><th class="text-center">Aksi</th></tr></thead>
        <tbody>
          @forelse ($receivables as $r)
            <tr>
              <td class="table-product-name">{{ $r->customer->customer_name ?? '-' }}</td>
              <td>{{ $r->note ?: '-' }}</td>
              <td>{{ rupiah($r->amount) }}</td>
              <td class="table-amount">{{ rupiah($r->remaining) }}</td>
              <td>
                {{ tanggal($r->due_date) }}
                @if ($r->is_overdue)<span class="badge-table failed ms-1">Terlambat</span>@endif
              </td>
              <td><x-status :value="$r->status" /></td>
              <td>
                <div class="d-flex justify-content-center gap-1">
                  <a href="{{ route('piutang.show', $r) }}" class="table-btn-action" title="Detail & terima bayar"><i class="bi bi-eye"></i></a>
                  <a href="{{ route('piutang.edit', $r) }}" class="table-btn-action" title="Ubah"><i class="bi bi-pencil"></i></a>
                  @unless ($r->id_transaction)
                    <x-delete-form :action="route('piutang.destroy', $r)" message="Hapus catatan piutang ini beserta riwayat cicilannya?" />
                  @endunless
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted-green py-4">Tidak ada catatan piutang.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <x-table-footer :paginator="$receivables" />
  </div>
@endsection
