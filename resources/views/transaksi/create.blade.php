@extends('template.app')
@section('title', 'Penjualan Baru')
@section('subtitle', 'Pilih item, isi jumlah, lalu simpan. Stok berkurang otomatis.')

@section('content')
  <form action="{{ route('transaksi.store') }}" method="POST" id="sale-form">
    @csrf
    <div class="row g-4">

            <div class="col-12 col-xl-8">
        <div class="card border-light shadow-sm p-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">Item</h5>
            <button type="button" class="btn-custom btn-custom-outline-primary btn-custom-sm" id="add-row"><i class="bi bi-plus-lg"></i> Tambah Baris</button>
          </div>

          <div class="table-responsive">
            <table class="table-custom">
              <thead><tr><th style="min-width:220px">Item</th><th style="width:120px">Jumlah</th><th>Harga</th><th>Subtotal</th><th></th></tr></thead>
              <tbody id="rows"></tbody>
            </table>
          </div>
          <p class="text-muted-green small mt-2 mb-0">Harga diambil dari data item. Stok yang tersedia tampil di samping nama item.</p>
        </div>
      </div>

            <div class="col-12 col-xl-4">
        <div class="card border-light shadow-sm p-4">
          <h5 class="card-title mb-3">Pembayaran</h5>

          <x-input name="transaction_date" label="Tanggal & Jam" type="datetime-local" :value="now()->format('Y-m-d\TH:i')" required />
          <x-select name="id_customer" label="Pelanggan (opsional)" :options="$customers->pluck('customer_name', 'id_customer')" placeholder="- Pelanggan umum -" />
          <a href="{{ route('pelanggan.create') }}" class="small d-block mb-3" target="_blank">+ Tambah pelanggan baru</a>

          <x-select name="payment_method" label="Metode Bayar" :options="['cash' => 'Tunai', 'transfer' => 'Transfer', 'qris' => 'QRIS', 'e-wallet' => 'E-Wallet']" value="cash" required />
          <x-select name="payment_status" label="Status Pembayaran" :options="['paid' => 'Lunas', 'unpaid' => 'Belum Lunas', 'down_payment' => 'DP (bayar sebagian)']" value="paid" required />

          <div id="dp-field" class="d-none">
            <x-input name="paid_amount" label="Nominal DP (Rp)" type="number" min="0" step="any" />
          </div>
          <p id="customer-hint" class="small text-danger d-none">Pelanggan wajib dipilih agar sisa tagihan tercatat sebagai piutang.</p>

          <x-input name="note" label="Catatan (opsional)" />

          <div class="d-flex justify-content-between align-items-center border-top pt-3 mb-3">
            <span class="stat-label">Total</span>
            <span class="stat-value mb-0" id="grand-total">Rp 0</span>
          </div>
          <button type="submit" class="btn-custom btn-custom-primary w-100">Simpan Penjualan</button>
        </div>
      </div>
    </div>
  </form>
@endsection

@php
  $itemData = $items->map(fn ($i) => [
      'id' => $i->id_item, 'name' => $i->item_name, 'price' => (float) $i->selling_price,
      'service' => (bool) $i->is_service, 'stock' => (float) $i->stock, 'unit' => $i->unit->unit_name ?? '',
  ])->values();
  $oldRows = old('items') ? array_values(old('items')) : [['id_item' => '', 'quantity' => 1]];
@endphp

@push('scripts')
<script>
  const ITEMS = @json($itemData);
  const OLD_ROWS = @json($oldRows);

  const rows = document.getElementById('rows');
  const rupiah = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');

  function addRow(data = {}) {
    const index = rows.children.length + Date.now(); // indeks unik agar nama field tidak bentrok
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><select name="items[${index}][id_item]" class="form-select-custom item-select" required>
            <option value="">- Pilih item -</option>
            ${ITEMS.map(i => `<option value="${i.id}">${i.name}${i.service ? '' : ` (stok ${i.stock} ${i.unit})`}</option>`).join('')}
          </select></td>
      <td><input type="number" name="items[${index}][quantity]" class="form-control-custom qty" min="0.01" step="any" value="${data.quantity ?? 1}" required></td>
      <td class="price">-</td>
      <td class="subtotal">-</td>
      <td><button type="button" class="table-btn-action delete remove" title="Hapus baris"><i class="bi bi-x-lg"></i></button></td>`;
    rows.appendChild(tr);
    tr.querySelector('.item-select').value = data.id_item ?? '';
    recalc();
  }

  function recalc() {
    let total = 0;
    rows.querySelectorAll('tr').forEach(tr => {
      const item = ITEMS.find(i => i.id == tr.querySelector('.item-select').value);
      const qty = parseFloat(tr.querySelector('.qty').value) || 0;
      const sub = item ? item.price * qty : 0;
      tr.querySelector('.price').textContent = item ? rupiah(item.price) : '-';
      tr.querySelector('.subtotal').textContent = item ? rupiah(sub) : '-';
      total += sub;
    });
    document.getElementById('grand-total').textContent = rupiah(total);
  }

  rows.addEventListener('input', recalc);
  rows.addEventListener('change', recalc);
  rows.addEventListener('click', e => {
    const btn = e.target.closest('.remove');
    if (btn && rows.children.length > 1) { btn.closest('tr').remove(); recalc(); }
  });
  document.getElementById('add-row').addEventListener('click', () => addRow());
  OLD_ROWS.forEach(r => addRow(r));

  const status = document.getElementById('payment_status');
  function togglePayment() {
    document.getElementById('dp-field').classList.toggle('d-none', status.value !== 'down_payment');
    document.getElementById('customer-hint').classList.toggle('d-none', status.value === 'paid');
  }
  status.addEventListener('change', togglePayment);
  togglePayment();
</script>
@endpush
