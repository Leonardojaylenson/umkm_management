@extends('template.app')
@section('title', $item->exists ? 'Ubah Item' : 'Tambah Item')
@section('subtitle', 'Isi data barang atau layanan. Stok diubah lewat halaman detail item.')

@section('content')
  <div class="card border-light shadow-sm p-4 mx-auto w-100" style="max-width: 800px">
    <form action="{{ $item->exists ? route('item.update', $item->id_item) : route('item.store') }}" method="POST" enctype="multipart/form-data">
      @csrf
      @if ($item->exists)
        @method('PUT')
      @endif

      <div class="mb-3">
        <label class="form-label-custom">Nama Item</label>
        <input type="text" name="item_name" class="form-control-custom" value="{{ old('item_name', $item->item_name) }}" required>
        @error('item_name')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label-custom">Kategori</label>
          <select name="id_category" class="form-select-custom">
            <option value="">- Tanpa kategori -</option>
            @foreach ($categories as $category)
              <option value="{{ $category->id_category }}" {{ old('id_category', $item->id_category) == $category->id_category ? 'selected' : '' }}>{{ $category->category_name }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label-custom">Satuan</label>
          <select name="id_unit" class="form-select-custom">
            <option value="">- Tanpa satuan -</option>
            @foreach ($units as $unit)
              <option value="{{ $unit->id_unit }}" {{ old('id_unit', $item->id_unit) == $unit->id_unit ? 'selected' : '' }}>{{ $unit->unit_name }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label-custom">Harga Jual (Rp)</label>
          <input type="number" name="selling_price" min="0" step="any" class="form-control-custom" value="{{ old('selling_price', $item->selling_price) }}" required>
          @error('selling_price')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label-custom">Harga Modal (Rp)</label>
          <input type="number" name="cost_price" min="0" step="any" class="form-control-custom" value="{{ old('cost_price', $item->cost_price ?? 0) }}" required>
          @error('cost_price')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="form-check-custom mb-3">
        <input type="checkbox" class="form-check-input-custom" id="is_service" name="is_service" value="1" {{ old('is_service', $item->is_service) ? 'checked' : '' }}>
        <label for="is_service" class="form-label-custom mb-0">Tanpa stok (layanan / jasa)</label>
      </div>

      <div id="stock-fields" class="row">
        @if (! $item->exists)
          <div class="col-md-6 mb-3">
            <label class="form-label-custom">Stok Awal</label>
            <input type="number" name="stock" min="0" step="any" class="form-control-custom" value="{{ old('stock', 0) }}">
            @error('stock')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
          </div>
        @endif

        <div class="col-md-6 mb-3">
          <label class="form-label-custom">Batas Stok Menipis</label>
          <input type="number" name="min_stock" min="0" step="any" class="form-control-custom" value="{{ old('min_stock', $item->min_stock ?? 0) }}">
          <small class="text-muted">Peringatan muncul jika stok sama dengan atau di bawah angka ini.</small>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label-custom">Foto (opsional, maksimal 2 MB)</label>
        <input type="file" name="photo" accept="image/*" class="form-control-custom">
        @error('photo')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
        @if ($item->photo)
          <img src="{{ asset('storage/' . $item->photo) }}" alt="" class="rounded mt-2" style="height:70px">
        @endif
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn-custom btn-custom-primary">Simpan</button>
        <a href="{{ route('item.index') }}" class="btn-custom btn-custom-light">Batal</a>
      </div>
    </form>
  </div>
@endsection

@push('scripts')
<script>
  var serviceBox = document.getElementById('is_service');
  var stockFields = document.getElementById('stock-fields');

  function toggleStock() {
    if (serviceBox.checked) {
      stockFields.classList.add('d-none');
    } else {
      stockFields.classList.remove('d-none');
    }
  }

  serviceBox.addEventListener('change', toggleStock);
  toggleStock();
</script>
@endpush
