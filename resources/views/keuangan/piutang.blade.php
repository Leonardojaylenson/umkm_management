@extends('template.app')
@section('title', 'Piutang Pelanggan')
@section('subtitle', 'Tagihan pelanggan yang belum dibayar (otomatis dari transaksi belum lunas atau DP).')

@section('content')
  <div class="row g-4 mb-4">
    <div class="col-12 col-md-4">
      <div class="card card-stat h-100">
        <div class="card-header">
          <span class="stat-label">Total Piutang Belum Lunas</span>
          <i class="bi bi-cash-stack text-muted-green fs-5"></i>
        </div>
        <div class="stat-value">{{ rupiah($outstanding) }}</div>
      </div>
    </div>
  </div>

  <div class="table-card-custom">
    <form class="table-header-control" method="GET">
      <div class="table-filter-group">
        <select name="status" class="form-select-custom form-control-custom-sm" onchange="this.form.submit()">
          <option value="">Semua Status</option>
          <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Belum Lunas</option>
          <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Dicicil</option>
          <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Lunas</option>
        </select>
        <a href="{{ route('hutang.index') }}" class="btn-custom btn-custom-light btn-custom-sm">Hutang</a>
      </div>
      <a href="{{ route('piutang.create') }}" class="btn-custom btn-custom-primary btn-custom-sm"><i class="bi bi-plus-lg"></i> Catat Piutang</a>
    </form>

    <div class="table-responsive">
      <table class="table-custom">
        <thead>
          <tr><th>Pelanggan</th><th>Catatan</th><th>Total</th><th>Sisa</th><th>Jatuh Tempo</th><th>Status</th><th class="text-center">Aksi</th></tr>
        </thead>
        <tbody>
          @forelse ($receivables as $r)
            <tr>
              <td class="table-product-name">{{ $r->customer->customer_name ?? '-' }}</td>
              <td>{{ $r->note ?: '-' }}</td>
              <td>{{ rupiah($r->amount) }}</td>
              <td class="table-amount">{{ rupiah($r->amount - $r->paid_amount) }}</td>
              <td>
                {{ tanggal($r->due_date) }}
                @if ($r->status != 'paid' && $r->due_date && $r->due_date->lt(today()))
                  <span class="badge-table failed ms-1">Terlambat</span>
                @endif
              </td>
              <td>{!! badge($r->status) !!}</td>
              <td>
                <div class="d-flex justify-content-center gap-1">
                  <a href="{{ route('piutang.show', $r->id_receivable) }}" class="table-btn-action" title="Detail & terima bayar"><i class="bi bi-eye"></i></a>
                  <a href="{{ route('piutang.edit', $r->id_receivable) }}" class="table-btn-action" title="Ubah"><i class="bi bi-pencil"></i></a>

                  @if (! $r->id_transaction)
                    <form action="{{ route('piutang.destroy', $r->id_receivable) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus piutang ini beserta cicilannya?')">
                      @csrf
                      @method('DELETE')
                      <button class="table-btn-action delete" title="Hapus"><i class="bi bi-trash"></i></button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted-green py-4">Tidak ada catatan piutang.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="table-footer-control">{{ $receivables->links() }}</div>
  </div>
@endsection
