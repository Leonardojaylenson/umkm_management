@extends('template.app')
@section('title', 'Detail Item')
@section('subtitle', $item->item_name)

@section('content')
  <div class="row g-4 mb-4">

    <div class="col-12 col-lg-7">
      <div class="card border-light shadow-sm p-4 h-100">
        <div class="d-flex gap-3">
          @if ($item->photo)
            <img src="{{ asset('storage/' . $item->photo) }}" alt="" class="rounded" style="width:100px;height:100px;object-fit:cover">
          @endif

          <dl class="row mb-0 flex-grow-1">
            <dt class="col-5">Kategori</dt><dd class="col-7">{{ $item->category->category_name ?? '-' }}</dd>
            <dt class="col-5">Satuan</dt><dd class="col-7">{{ $item->unit->unit_name ?? '-' }}</dd>
            <dt class="col-5">Harga Jual</dt><dd class="col-7">{{ rupiah($item->selling_price) }}</dd>
            <dt class="col-5">Harga Modal</dt><dd class="col-7">{{ rupiah($item->cost_price) }}</dd>
            <dt class="col-5">Stok</dt>
            <dd class="col-7">
              @if ($item->is_service)
                Tanpa stok (layanan)
              @else
                <strong>{{ qty($item->stock) }}</strong> {{ $item->unit->unit_name ?? '' }}
                @if ($item->stock <= $item->min_stock)
                  <span class="badge-table pending ms-1">Menipis</span>
                @endif
              @endif
            </dd>
            @if (! $item->is_service)
              <dt class="col-5">Batas Menipis</dt><dd class="col-7">{{ qty($item->min_stock) }}</dd>
            @endif
          </dl>
        </div>

        <div class="d-flex gap-2 mt-4">
          <a href="{{ route('item.edit', $item->id_item) }}" class="btn-custom btn-custom-primary">Ubah Item</a>
          <a href="{{ route('item.index') }}" class="btn-custom btn-custom-light">Kembali</a>
        </div>
      </div>
    </div>

    @if (! $item->is_service)
      <div class="col-12 col-lg-5">
        <div class="card border-light shadow-sm p-4 h-100">
          <h5 class="card-title mb-3">Ubah Stok</h5>

          <form action="{{ route('item.stock', $item->id_item) }}" method="POST">
            @csrf

            <div class="mb-3">
              <label class="form-label-custom">Jenis</label>
              <select name="type" class="form-select-custom">
                <option value="in">Stok masuk (tambah)</option>
                <option value="adjustment">Koreksi (isi dengan jumlah hasil hitung fisik)</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label-custom">Jumlah</label>
              <input type="number" name="quantity" min="0" step="any" class="form-control-custom" required>
              @error('quantity')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
              <label class="form-label-custom">Catatan (opsional)</label>
              <input type="text" name="note" class="form-control-custom">
            </div>

            <button type="submit" class="btn-custom btn-custom-primary">Simpan Stok</button>
          </form>
        </div>
      </div>
    @endif

  </div>

  @if (! $item->is_service)
    @php
      $jenis = ['in' => 'Stok masuk', 'adjustment' => 'Koreksi', 'sale' => 'Terjual', 'cancellation' => 'Batal transaksi'];
    @endphp

    <div class="table-card-custom">
      <div class="table-header-control"><h5 class="card-title mb-0">Riwayat Stok</h5></div>
      <div class="table-responsive">
        <table class="table-custom">
          <thead>
            <tr><th>Waktu</th><th>Jenis</th><th>Perubahan</th><th>Stok Sebelum</th><th>Stok Sesudah</th><th>Oleh</th><th>Catatan</th></tr>
          </thead>
          <tbody>
            @forelse ($histories as $h)
              <tr>
                <td>{{ tanggal($h->created_at, true) }}</td>
                <td>{{ $jenis[$h->type] }}</td>
                <td class="{{ $h->quantity >= 0 ? 'text-success' : 'text-danger' }}">{{ $h->quantity > 0 ? '+' : '' }}{{ qty($h->quantity) }}</td>
                <td>{{ qty($h->stock_before) }}</td>
                <td>{{ qty($h->stock_after) }}</td>
                <td>{{ $h->user->name ?? '-' }}</td>
                <td>{{ $h->note ?: '-' }}</td>
              </tr>
            @empty
              <tr><td colspan="7" class="text-center text-muted-green py-4">Belum ada riwayat stok.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="table-footer-control">{{ $histories->links() }}</div>
    </div>
  @endif
@endsection
