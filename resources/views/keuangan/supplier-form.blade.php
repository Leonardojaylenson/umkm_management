@extends('template.app')
@php $isNew = ! $supplier->exists; @endphp
@section('title', $isNew ? 'Tambah Supplier' : 'Ubah Supplier')
@section('subtitle', 'Data supplier untuk pencatatan hutang.')

@section('content')
  <div class="card border-light shadow-sm p-4" style="max-width: 640px">
    <form action="{{ $isNew ? route('supplier.store') : route('supplier.update', $supplier) }}" method="POST">
      @csrf
      @unless ($isNew) @method('PUT') @endunless

      <x-input name="supplier_name" label="Nama Supplier" :value="$supplier->supplier_name" required />
      <x-input name="phone" label="No. Telepon" :value="$supplier->phone" />
      <x-textarea name="address" label="Alamat" :value="$supplier->address" />

      <div class="d-flex gap-2">
        <button type="submit" class="btn-custom btn-custom-primary">Simpan</button>
        <a href="{{ route('supplier.index') }}" class="btn-custom btn-custom-light">Batal</a>
      </div>
    </form>
  </div>
@endsection
