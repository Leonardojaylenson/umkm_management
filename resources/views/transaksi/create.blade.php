@extends('template.app')
@section('title', 'Penjualan Baru')
@section('subtitle', 'Pilih item, isi jumlah, lalu simpan. Stok berkurang otomatis.')

@section('content')
  <form action="{{ route('transaksi.store') }}" method="POST">
    @csrf

    <div class="row g-4">

      <div class="col-12 col-xl-8">
        <div class="card border-light shadow-sm p-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">Item</h5>
            <button type="button" class="btn-custom btn-custom-outline-primary btn-custom-sm" onclick="tambahBaris()"><i class="bi bi-plus-lg"></i> Tambah Baris</button>
          </div>

          <div class="table-responsive">
            <table class="table-custom">
              <thead>
                <tr><th style="min-width:220px">Item</th><th style="width:120px">Jumlah</th><th>Harga</th><th>Subtotal</th><th></th></tr>
              </thead>
              <tbody id="baris-item"></tbody>
            </table>
          </div>
          <p class="text-muted-green small mt-2 mb-0">Harga diambil dari data item.</p>
        </div>
      </div>

      <div class="col-12 col-xl-4">
        <div class="card border-light shadow-sm p-4">
          <h5 class="card-title mb-3">Pembayaran</h5>

          <div class="mb-3">
            <label class="form-label-custom">Tanggal &amp; Jam</label>
            <input type="datetime-local" name="transaction_date" class="form-control-custom" value="{{ old('transaction_date', now()->format('Y-m-d\TH:i')) }}" required>
            @error('transaction_date')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
          </div>

          <div class="mb-3">
            <label class="form-label-custom">Pelanggan (opsional)</label>
            <select name="id_customer" class="form-select-custom">
              <option value="">- Pelanggan umum -</option>
              @foreach ($customers as $customer)
                <option value="{{ $customer->id_customer }}" {{ old('id_customer') == $customer->id_customer ? 'selected' : '' }}>{{ $customer->customer_name }}</option>
              @endforeach
            </select>
            @error('id_customer')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
            <a href="{{ route('pelanggan.create') }}" class="small" target="_blank">+ Tambah pelanggan baru</a>
          </div>

          <div class="mb-3">
            <label class="form-label-custom">Metode Bayar</label>
            <select name="payment_method" class="form-select-custom">
              <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Tunai</option>
              <option value="transfer" {{ old('payment_method') == 'transfer' ? 'selected' : '' }}>Transfer</option>
              <option value="qris" {{ old('payment_method') == 'qris' ? 'selected' : '' }}>QRIS</option>
              <option value="e-wallet" {{ old('payment_method') == 'e-wallet' ? 'selected' : '' }}>E-Wallet</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label-custom">Status Pembayaran</label>
            <select name="payment_status" id="payment_status" class="form-select-custom" onchange="aturBayar()">
              <option value="paid" {{ old('payment_status') == 'paid' ? 'selected' : '' }}>Lunas</option>
              <option value="unpaid" {{ old('payment_status') == 'unpaid' ? 'selected' : '' }}>Belum Lunas</option>
              <option value="down_payment" {{ old('payment_status') == 'down_payment' ? 'selected' : '' }}>DP (bayar sebagian)</option>
            </select>
          </div>

          <div id="kolom-dp" class="mb-3 d-none">
            <label class="form-label-custom">Nominal DP (Rp)</label>
            <input type="number" name="paid_amount" min="0" step="any" class="form-control-custom" value="{{ old('paid_amount') }}">
            @error('paid_amount')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
          </div>

          <p id="info-pelanggan" class="small text-danger d-none">Pelanggan wajib dipilih agar sisa tagihan tercatat sebagai piutang.</p>

          <div class="mb-3">
            <label class="form-label-custom">Catatan (opsional)</label>
            <input type="text" name="note" class="form-control-custom" value="{{ old('note') }}">
          </div>

          <div class="d-flex justify-content-between align-items-center border-top pt-3 mb-3">
            <span class="stat-label">Total</span>
            <span class="stat-value mb-0" id="total-akhir">Rp 0</span>
          </div>

          <button type="submit" class="btn-custom btn-custom-primary w-100">Simpan Penjualan</button>
        </div>
      </div>

    </div>
  </form>
@endsection

@php
  $dataItem = [];
  foreach ($items as $item) {
    $dataItem[] = [
      'id'      => $item->id_item,
      'nama'    => $item->item_name,
      'harga'   => (float) $item->selling_price,
      'layanan' => (bool) $item->is_service,
      'stok'    => (float) $item->stock,
      'satuan'  => $item->unit->unit_name ?? '',
    ];
  }

  $barisLama = old('items') ? array_values(old('items')) : [['id_item' => '', 'quantity' => 1]];
@endphp

@push('scripts')
<script>
  var daftarItem = @json($dataItem);
  var barisLama = @json($barisLama);
  var tabel = document.getElementById('baris-item');
  var nomor = 0;

  function formatRupiah(angka) {
    return 'Rp ' + Math.round(angka).toLocaleString('id-ID');
  }

  function tambahBaris(idItem, jumlah) {
    nomor = nomor + 1;

    var pilihan = '<option value="">- Pilih item -</option>';
    for (var i = 0; i < daftarItem.length; i++) {
      var item = daftarItem[i];
      var teks = item.nama;
      if (!item.layanan) {
        teks = teks + ' (stok ' + item.stok + ' ' + item.satuan + ')';
      }
      var terpilih = (item.id == idItem) ? ' selected' : '';
      pilihan = pilihan + '<option value="' + item.id + '"' + terpilih + '>' + teks + '</option>';
    }

    var baris = document.createElement('tr');
    baris.innerHTML =
      '<td><select name="items[' + nomor + '][id_item]" class="form-select-custom pilih-item" onchange="hitungTotal()" required>' + pilihan + '</select></td>' +
      '<td><input type="number" name="items[' + nomor + '][quantity]" class="form-control-custom jumlah" min="0.01" step="any" value="' + (jumlah || 1) + '" oninput="hitungTotal()" required></td>' +
      '<td class="harga">-</td>' +
      '<td class="subtotal">-</td>' +
      '<td><button type="button" class="table-btn-action delete" onclick="hapusBaris(this)"><i class="bi bi-x-lg"></i></button></td>';

    tabel.appendChild(baris);
    hitungTotal();
  }

  function hapusBaris(tombol) {
    if (tabel.children.length > 1) {
      tombol.closest('tr').remove();
      hitungTotal();
    }
  }

  function hitungTotal() {
    var total = 0;

    for (var i = 0; i < tabel.children.length; i++) {
      var baris = tabel.children[i];
      var idItem = baris.querySelector('.pilih-item').value;
      var jumlah = parseFloat(baris.querySelector('.jumlah').value) || 0;

      var item = null;
      for (var j = 0; j < daftarItem.length; j++) {
        if (daftarItem[j].id == idItem) {
          item = daftarItem[j];
        }
      }

      if (item) {
        var subtotal = item.harga * jumlah;
        baris.querySelector('.harga').textContent = formatRupiah(item.harga);
        baris.querySelector('.subtotal').textContent = formatRupiah(subtotal);
        total = total + subtotal;
      } else {
        baris.querySelector('.harga').textContent = '-';
        baris.querySelector('.subtotal').textContent = '-';
      }
    }

    document.getElementById('total-akhir').textContent = formatRupiah(total);
  }

  function aturBayar() {
    var status = document.getElementById('payment_status').value;

    if (status == 'down_payment') {
      document.getElementById('kolom-dp').classList.remove('d-none');
    } else {
      document.getElementById('kolom-dp').classList.add('d-none');
    }

    if (status == 'paid') {
      document.getElementById('info-pelanggan').classList.add('d-none');
    } else {
      document.getElementById('info-pelanggan').classList.remove('d-none');
    }
  }

  for (var k = 0; k < barisLama.length; k++) {
    tambahBaris(barisLama[k].id_item, barisLama[k].quantity);
  }
  aturBayar();
</script>
@endpush
