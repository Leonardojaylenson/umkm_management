@extends('template.app')
@section('title', 'Detail Pelanggan')
@section('subtitle', $customer->customer_name)

@section('content')
  <div class="row g-4">
    <div class="col-12 col-lg-4">
      <div class="card border-light shadow-sm p-4">
        <dl class="row mb-3">
          <dt class="col-4">Nama</dt><dd class="col-8">{{ $customer->customer_name }}</dd>
          <dt class="col-4">Telepon</dt><dd class="col-8">{{ $customer->phone ?: '-' }}</dd>
          <dt class="col-4">Alamat</dt><dd class="col-8">{{ $customer->address ?: '-' }}</dd>
        </dl>
        <div class="d-flex gap-2">
          <a href="{{ route('pelanggan.edit', $customer) }}" class="btn-custom btn-custom-primary btn-custom-sm">Ubah</a>
          <a href="{{ route('pelanggan.index') }}" class="btn-custom btn-custom-light btn-custom-sm">Kembali</a>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-8">
      @if ($receivables->isNotEmpty())
        <div class="alert-custom alert-custom-warning">
          <i class="bi bi-exclamation-circle-fill alert-custom-icon"></i>
          <div class="alert-custom-content">Piutang belum lunas: <strong>{{ rupiah($receivables->sum->remaining) }}</strong></div>
        </div>
      @endif

      <div class="table-card-custom">
        <div class="table-header-control"><h5 class="card-title mb-0">10 Transaksi Terakhir</h5></div>
        <div class="table-responsive">
          <table class="table-custom">
            <thead><tr><th>No. Nota</th><th>Tanggal</th><th>Total</th><th>Pembayaran</th><th>Status</th></tr></thead>
            <tbody>
              @forelse ($transactions as $trx)
                <tr>
                  <td class="table-order-id"><a href="{{ route('transaksi.show', $trx) }}">{{ $trx->invoice_number }}</a></td>
                  <td>{{ tanggal($trx->transaction_date) }}</td>
                  <td class="table-amount">{{ rupiah($trx->total_amount) }}</td>
                  <td><x-status :value="$trx->payment_status" /></td>
                  <td><x-status :value="$trx->status" /></td>
                </tr>
              @empty
                <tr><td colspan="5" class="text-center text-muted-green py-4">Belum ada transaksi.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
@endsection
