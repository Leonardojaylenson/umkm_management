@extends('template.app')
@section('title', 'Koreksi Transaksi')
@section('subtitle', $transaction->invoice_number . ' - total ' . rupiah($transaction->total_amount))

@section('content')
  <div class="card border-light shadow-sm p-4" style="max-width: 640px">
    <p class="text-muted-green small">
      Item dan jumlah tidak bisa diubah agar stok tetap akurat. Jika item salah, batalkan transaksi lalu catat ulang.
    </p>

    <form action="{{ route('transaksi.update', $transaction) }}" method="POST">
      @csrf @method('PUT')

      <x-input name="transaction_date" label="Tanggal & Jam" type="datetime-local" :value="$transaction->transaction_date->format('Y-m-d\TH:i')" required />
      <x-select name="id_customer" label="Pelanggan" :options="$customers->pluck('customer_name', 'id_customer')" :value="$transaction->id_customer" placeholder="- Pelanggan umum -" />
      <x-select name="payment_method" label="Metode Bayar" :options="['cash' => 'Tunai', 'transfer' => 'Transfer', 'qris' => 'QRIS', 'e-wallet' => 'E-Wallet']" :value="$transaction->payment_method" required />
      <x-select name="payment_status" label="Status Pembayaran" :options="['paid' => 'Lunas', 'unpaid' => 'Belum Lunas', 'down_payment' => 'DP (bayar sebagian)']" :value="$transaction->payment_status" required />

      <div id="dp-field" class="d-none">
        <x-input name="paid_amount" label="Nominal DP (Rp)" type="number" min="0" step="any" :value="$transaction->payment_status === 'down_payment' ? $transaction->paid_amount : null" />
      </div>

      <x-input name="note" label="Catatan" :value="$transaction->note" />

      <div class="d-flex gap-2">
        <button type="submit" class="btn-custom btn-custom-primary">Simpan Koreksi</button>
        <a href="{{ route('transaksi.show', $transaction) }}" class="btn-custom btn-custom-light">Batal</a>
      </div>
    </form>
  </div>
@endsection

@push('scripts')
<script>
  const status = document.getElementById('payment_status');
  const toggleDp = () => document.getElementById('dp-field').classList.toggle('d-none', status.value !== 'down_payment');
  status.addEventListener('change', toggleDp);
  toggleDp();
</script>
@endpush
