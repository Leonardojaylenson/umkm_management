@extends('template.app')
@section('title', $supplier->exists ? 'Ubah Supplier' : 'Tambah Supplier')
@section('subtitle', 'Data supplier untuk pencatatan hutang.')

@section('content')
  <div class="card border-light shadow-sm p-4 mx-auto w-100" style="max-width: 640px">
    <form action="{{ $supplier->exists ? route('supplier.update', $supplier->id_supplier) : route('supplier.store') }}" method="POST">
      @csrf
      @if ($supplier->exists)
        @method('PUT')
      @endif

      <div class="mb-3">
        <label class="form-label-custom">Nama Supplier</label>
        <input type="text" name="supplier_name" class="form-control-custom" value="{{ old('supplier_name', $supplier->supplier_name) }}" required>
        @error('supplier_name')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
      </div>

      <div class="mb-3">
        <label class="form-label-custom">No. Telepon</label>
        <input type="text" name="phone" class="form-control-custom" value="{{ old('phone', $supplier->phone) }}">
      </div>

      <div class="mb-3">
        <label class="form-label-custom">Alamat</label>
        <textarea name="address" rows="3" class="form-control-custom">{{ old('address', $supplier->address) }}</textarea>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn-custom btn-custom-primary">Simpan</button>
        <a href="{{ route('supplier.index') }}" class="btn-custom btn-custom-light">Batal</a>
      </div>
    </form>
  </div>
@endsection
