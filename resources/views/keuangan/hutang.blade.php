@extends('template.app')
@section('title', 'Hutang ke Supplier')
@section('subtitle', 'Kewajiban usaha yang belum dibayar.')

@section('content')
  <div class="row g-4 mb-4">
    <div class="col-12 col-md-4"><x-stat-card label="Total Hutang Belum Lunas" :value="rupiah($outstanding)" icon="bi-cash-coin" /></div>
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
        <a href="{{ route('supplier.index') }}" class="btn-custom btn-custom-light btn-custom-sm">Data Supplier</a>
      </div>
      <a href="{{ route('hutang.create') }}" class="btn-custom btn-custom-primary btn-custom-sm"><i class="bi bi-plus-lg"></i> Catat Hutang</a>
    </form>

    <div class="table-responsive">
      <table class="table-custom">
        <thead><tr><th>Supplier</th><th>Catatan</th><th>Total</th><th>Sisa</th><th>Jatuh Tempo</th><th>Status</th><th class="text-center">Aksi</th></tr></thead>
        <tbody>
          @forelse ($debts as $debt)
            <tr>
              <td class="table-product-name">{{ $debt->supplier->supplier_name ?? '-' }}</td>
              <td>{{ $debt->note ?: '-' }}</td>
              <td>{{ rupiah($debt->amount) }}</td>
              <td class="table-amount">{{ rupiah($debt->remaining) }}</td>
              <td>
                {{ tanggal($debt->due_date) }}
                @if ($debt->is_overdue)<span class="badge-table failed ms-1">Terlambat</span>@endif
              </td>
              <td><x-status :value="$debt->status" /></td>
              <td>
                <div class="d-flex justify-content-center gap-1">
                  <a href="{{ route('hutang.show', $debt) }}" class="table-btn-action" title="Detail & bayar"><i class="bi bi-eye"></i></a>
                  <a href="{{ route('hutang.edit', $debt) }}" class="table-btn-action" title="Ubah"><i class="bi bi-pencil"></i></a>
                  <x-delete-form :action="route('hutang.destroy', $debt)" message="Hapus catatan hutang ini beserta riwayat cicilannya?" />
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted-green py-4">Tidak ada catatan hutang.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <x-table-footer :paginator="$debts" />
  </div>
@endsection
