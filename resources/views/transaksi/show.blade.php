@extends('template.app')
@section('title', 'Detail Transaksi')
@section('subtitle', $transaction->invoice_number)

@section('content')
  <div class="row g-4">
    <div class="col-12 col-xl-8">
      <div class="table-card-custom">
        <div class="table-header-control">
          <h5 class="card-title mb-0">Item Terjual</h5>
          <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('transaksi.print', $transaction) }}" target="_blank" class="btn-custom btn-custom-outline-primary btn-custom-sm"><i class="bi bi-printer"></i> Cetak Nota</a>
            @unless ($transaction->isCancelled())
              <a href="{{ route('transaksi.edit', $transaction) }}" class="btn-custom btn-custom-light btn-custom-sm"><i class="bi bi-pencil"></i> Koreksi</a>
              <form action="{{ route('transaksi.cancel', $transaction) }}" method="POST"
                onsubmit="return confirm('Batalkan transaksi ini? Stok akan dikembalikan.')">
                @csrf
                <button class="btn-custom btn-custom-outline-danger btn-custom-sm"><i class="bi bi-x-circle"></i> Batalkan</button>
              </form>
            @endunless
          </div>
        </div>
        <div class="table-responsive">
          <table class="table-custom">
            <thead><tr><th>Item</th><th>Harga</th><th>Jumlah</th><th class="text-end">Subtotal</th></tr></thead>
            <tbody>
              @foreach ($transaction->details as $d)
                <tr>
                  <td class="table-product-name">{{ $d->item_name }}</td>
                  <td>{{ rupiah($d->selling_price) }}</td>
                  <td>{{ qty($d->quantity) }}</td>
                  <td class="text-end table-amount">{{ rupiah($d->subtotal) }}</td>
                </tr>
              @endforeach
              <tr><td colspan="3" class="text-end"><strong>Total</strong></td><td class="text-end table-amount">{{ rupiah($transaction->total_amount) }}</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-4">
      <div class="card border-light shadow-sm p-4">
        <dl class="row mb-0">
          <dt class="col-5">Tanggal</dt><dd class="col-7">{{ tanggal($transaction->transaction_date, true) }}</dd>
          <dt class="col-5">Pelanggan</dt><dd class="col-7">{{ $transaction->customer->customer_name ?? 'Umum' }}</dd>
          <dt class="col-5">Kasir</dt><dd class="col-7">{{ $transaction->cashier->name ?? '-' }}</dd>
          <dt class="col-5">Metode</dt><dd class="col-7">{{ strtoupper($transaction->payment_method) }}</dd>
          <dt class="col-5">Status</dt><dd class="col-7"><x-status :value="$transaction->status" /></dd>
          <dt class="col-5">Pembayaran</dt><dd class="col-7"><x-status :value="$transaction->payment_status" /></dd>
          <dt class="col-5">Dibayar</dt><dd class="col-7">{{ rupiah($transaction->paid_amount) }}</dd>
          <dt class="col-5">Sisa</dt><dd class="col-7">{{ rupiah($transaction->remaining) }}</dd>
          <dt class="col-5">Catatan</dt><dd class="col-7">{{ $transaction->note ?: '-' }}</dd>
        </dl>
        @if ($transaction->receivable && auth()->user()->isOwner())
          <a href="{{ route('piutang.show', $transaction->receivable) }}" class="btn-custom btn-custom-outline-primary btn-custom-sm mt-3">Lihat Piutang</a>
        @endif
      </div>
    </div>
  </div>
@endsection
