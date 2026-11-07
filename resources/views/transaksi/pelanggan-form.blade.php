@extends('template.app')
@php $isNew = ! $customer->exists; @endphp
@section('title', $isNew ? 'Tambah Pelanggan' : 'Ubah Pelanggan')
@section('subtitle', 'Nama wajib diisi; telepon dan alamat opsional.')

@section('content')
  <div class="card border-light shadow-sm p-4" style="max-width: 640px">
    <form action="{{ $isNew ? route('pelanggan.store') : route('pelanggan.update', $customer) }}" method="POST">
      @csrf
      @unless ($isNew) @method('PUT') @endunless

      <x-input name="customer_name" label="Nama Pelanggan" :value="$customer->customer_name" required />
      <x-input name="phone" label="No. Telepon" :value="$customer->phone" />
      <x-textarea name="address" label="Alamat" :value="$customer->address" />

      <div class="d-flex gap-2">
        <button type="submit" class="btn-custom btn-custom-primary">Simpan</button>
        <a href="{{ route('pelanggan.index') }}" class="btn-custom btn-custom-light">Batal</a>
      </div>
    </form>
  </div>
@endsection
