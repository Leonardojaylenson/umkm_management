@extends('template.app')
@section('title', 'Riwayat Transaksi')
@section('subtitle', 'Semua penjualan pada rentang tanggal yang dipilih.')

@section('content')
  <div class="table-card-custom">
    <form class="table-header-control" method="GET">
      <div class="table-search-box">
        <i class="bi bi-search table-search-icon"></i>
        <input type="text" name="q" value="{{ request('q') }}" class="table-search-input" placeholder="No. nota atau pelanggan...">
      </div>
      <div class="table-filter-group">
        <input type="date" name="from" value="{{ $from }}" class="form-control-custom form-control-custom-sm" title="Dari tanggal">
        <input type="date" name="to" value="{{ $to }}" class="form-control-custom form-control-custom-sm" title="Sampai tanggal">
        <select name="payment_status" class="form-select-custom form-control-custom-sm">
          <option value="">Semua Pembayaran</option>
          <option value="paid" @selected(request('payment_status') === 'paid')>Lunas</option>
          <option value="unpaid" @selected(request('payment_status') === 'unpaid')>Belum Lunas</option>
          <option value="down_payment" @selected(request('payment_status') === 'down_payment')>DP</option>
        </select>
        <select name="status" class="form-select-custom form-control-custom-sm">
          <option value="">Semua Status</option>
          <option value="completed" @selected(request('status') === 'completed')>Selesai</option>
          <option value="cancelled" @selected(request('status') === 'cancelled')>Dibatalkan</option>
        </select>
        <button class="btn-custom btn-custom-light btn-custom-sm">Terapkan</button>
        <a href="{{ route('transaksi.create') }}" class="btn-custom btn-custom-primary btn-custom-sm"><i class="bi bi-plus-lg"></i> Penjualan Baru</a>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table-custom">
        <thead>
          <tr><th>No. Nota</th><th>Tanggal</th><th>Pelanggan</th><th>Metode</th><th>Total</th><th>Pembayaran</th><th>Status</th><th class="text-center">Aksi</th></tr>
        </thead>
        <tbody>
          @forelse ($transactions as $trx)
            <tr>
              <td class="table-order-id">{{ $trx->invoice_number }}</td>
              <td>{{ tanggal($trx->transaction_date, true) }}</td>
              <td>{{ $trx->customer->customer_name ?? 'Umum' }}</td>
              <td>{{ strtoupper($trx->payment_method) }}</td>
              <td class="table-amount">{{ rupiah($trx->total_amount) }}</td>
              <td><x-status :value="$trx->payment_status" /></td>
              <td><x-status :value="$trx->status" /></td>
              <td>
                <div class="d-flex justify-content-center gap-1">
                  <a href="{{ route('transaksi.show', $trx) }}" class="table-btn-action" title="Detail"><i class="bi bi-eye"></i></a>
                  <a href="{{ route('transaksi.print', $trx) }}" target="_blank" class="table-btn-action" title="Cetak nota"><i class="bi bi-printer"></i></a>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-center text-muted-green py-4">Tidak ada transaksi pada filter ini.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <x-table-footer :paginator="$transactions" />
  </div>
@endsection
