@extends('template.app')
@section('title', 'Koreksi Transaksi')
@section('subtitle', $transaction->invoice_number . ' - total ' . rupiah($transaction->total_amount))

@section('content')
  <div class="card border-light shadow-sm p-4 mx-auto w-100" style="max-width: 640px">
    <p class="text-muted-green small">
      Item dan jumlah tidak bisa diubah agar stok tetap akurat. Jika item salah, batalkan transaksi lalu catat ulang.
    </p>

    <form action="{{ route('transaksi.update', $transaction->id_transaction) }}" method="POST">
      @csrf
      @method('PUT')

      <div class="mb-3">
        <label class="form-label-custom">Tanggal &amp; Jam</label>
        <input type="datetime-local" name="transaction_date" class="form-control-custom" value="{{ old('transaction_date', $transaction->transaction_date->format('Y-m-d\TH:i')) }}" required>
        @error('transaction_date')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
      </div>

      <div class="mb-3">
        <label class="form-label-custom">Pelanggan</label>
        <select name="id_customer" class="form-select-custom">
          <option value="">- Pelanggan umum -</option>
          @foreach ($customers as $customer)
            <option value="{{ $customer->id_customer }}" {{ old('id_customer', $transaction->id_customer) == $customer->id_customer ? 'selected' : '' }}>{{ $customer->customer_name }}</option>
          @endforeach
        </select>
        @error('id_customer')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
      </div>

      <div class="mb-3">
        <label class="form-label-custom">Metode Bayar</label>
        <select name="payment_method" class="form-select-custom">
          <option value="cash" {{ old('payment_method', $transaction->payment_method) == 'cash' ? 'selected' : '' }}>Tunai</option>
          <option value="transfer" {{ old('payment_method', $transaction->payment_method) == 'transfer' ? 'selected' : '' }}>Transfer</option>
          <option value="qris" {{ old('payment_method', $transaction->payment_method) == 'qris' ? 'selected' : '' }}>QRIS</option>
          <option value="e-wallet" {{ old('payment_method', $transaction->payment_method) == 'e-wallet' ? 'selected' : '' }}>E-Wallet</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label-custom">Status Pembayaran</label>
        <select name="payment_status" id="payment_status" class="form-select-custom" onchange="aturBayar()">
          <option value="paid" {{ old('payment_status', $transaction->payment_status) == 'paid' ? 'selected' : '' }}>Lunas</option>
          <option value="unpaid" {{ old('payment_status', $transaction->payment_status) == 'unpaid' ? 'selected' : '' }}>Belum Lunas</option>
          <option value="down_payment" {{ old('payment_status', $transaction->payment_status) == 'down_payment' ? 'selected' : '' }}>DP (bayar sebagian)</option>
        </select>
        @error('payment_status')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
      </div>

      <div id="kolom-dp" class="mb-3 d-none">
        <label class="form-label-custom">Nominal DP (Rp)</label>
        <input type="number" name="paid_amount" min="0" step="any" class="form-control-custom" value="{{ old('paid_amount', $transaction->payment_status == 'down_payment' ? $transaction->paid_amount : '') }}">
        @error('paid_amount')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
      </div>

      <div class="mb-3">
        <label class="form-label-custom">Catatan</label>
        <input type="text" name="note" class="form-control-custom" value="{{ old('note', $transaction->note) }}">
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn-custom btn-custom-primary">Simpan Koreksi</button>
        <a href="{{ route('transaksi.show', $transaction->id_transaction) }}" class="btn-custom btn-custom-light">Batal</a>
      </div>
    </form>
  </div>
@endsection

@push('scripts')
<script>
  function aturBayar() {
    var status = document.getElementById('payment_status').value;

    if (status == 'down_payment') {
      document.getElementById('kolom-dp').classList.remove('d-none');
    } else {
      document.getElementById('kolom-dp').classList.add('d-none');
    }
  }

  aturBayar();
</script>
@endpush
