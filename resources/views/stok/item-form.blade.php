@extends('template.app')
@php $isNew = ! $item->exists; @endphp
@section('title', $isNew ? 'Tambah Item' : 'Ubah Item')
@section('subtitle', $isNew ? 'Daftarkan barang atau layanan baru.' : 'Perbarui harga, kategori, atau foto. Stok diubah lewat halaman detail.')

@section('content')
  <div class="card border-light shadow-sm p-4" style="max-width: 800px">
    <form action="{{ $isNew ? route('item.store') : route('item.update', $item) }}" method="POST" enctype="multipart/form-data">
      @csrf
      @unless ($isNew) @method('PUT') @endunless

      <x-input name="item_name" label="Nama Item" :value="$item->item_name" required />

      <div class="row">
        <div class="col-md-6">
          <x-select name="id_category" label="Kategori" :options="$categories->pluck('category_name', 'id_category')" :value="$item->id_category" placeholder="- Tanpa kategori -" />
        </div>
        <div class="col-md-6">
          <x-select name="id_unit" label="Satuan" :options="$units->pluck('unit_name', 'id_unit')" :value="$item->id_unit" placeholder="- Tanpa satuan -" />
        </div>
        <div class="col-md-6"><x-input name="selling_price" label="Harga Jual (Rp)" type="number" min="0" step="any" :value="$item->selling_price" required /></div>
        <div class="col-md-6"><x-input name="cost_price" label="Harga Modal (Rp)" type="number" min="0" step="any" :value="$item->cost_price ?? 0" required /></div>
      </div>

      <div class="form-check-custom mb-3">
        <input type="checkbox" class="form-check-input-custom" id="is_service" name="is_service" value="1" @checked(old('is_service', $item->is_service))>
        <label for="is_service" class="form-label-custom mb-0">Tanpa stok (layanan / jasa)</label>
      </div>

            <div id="stock-fields" class="row">
        @if ($isNew)
          <div class="col-md-6"><x-input name="stock" label="Stok Awal" type="number" min="0" step="any" :value="0" /></div>
        @endif
        <div class="col-md-6"><x-input name="min_stock" label="Batas Stok Menipis" type="number" min="0" step="any" :value="$item->min_stock ?? 0" hint="Peringatan muncul jika stok sama atau di bawah angka ini." /></div>
      </div>

      <div class="mb-3">
        <label for="photo" class="form-label-custom">Foto (opsional, maks. 2 MB)</label>
        <input type="file" id="photo" name="photo" accept="image/*" class="form-control-custom">
        @error('photo')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
        @if ($item->photo)<img src="{{ asset('storage/' . $item->photo) }}" alt="" class="rounded mt-2" style="height:70px">@endif
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
  const serviceBox = document.getElementById('is_service');
  const stockFields = document.getElementById('stock-fields');
  const toggleStock = () => stockFields.classList.toggle('d-none', serviceBox.checked);
  serviceBox.addEventListener('change', toggleStock);
  toggleStock();
</script>
@endpush
