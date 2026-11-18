@extends('template.app')
@section('title', $customer->exists ? 'Ubah Pelanggan' : 'Tambah Pelanggan')
@section('subtitle', 'Nama wajib diisi; telepon dan alamat boleh kosong.')

@section('content')
  <div class="card border-light shadow-sm p-4 mx-auto w-100" style="max-width: 640px">
    <form action="{{ $customer->exists ? route('pelanggan.update', $customer->id_customer) : route('pelanggan.store') }}" method="POST">
      @csrf
      @if ($customer->exists)
        @method('PUT')
      @endif

      <div class="mb-3">
        <label class="form-label-custom">Nama Pelanggan</label>
        <input type="text" name="customer_name" class="form-control-custom" value="{{ old('customer_name', $customer->customer_name) }}" required>
        @error('customer_name')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
      </div>

      <div class="mb-3">
        <label class="form-label-custom">No. Telepon</label>
        <input type="text" name="phone" class="form-control-custom" value="{{ old('phone', $customer->phone) }}">
        @error('phone')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
      </div>

      <div class="mb-3">
        <label class="form-label-custom">Alamat</label>
        <textarea name="address" rows="3" class="form-control-custom">{{ old('address', $customer->address) }}</textarea>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn-custom btn-custom-primary">Simpan</button>
        <a href="{{ route('pelanggan.index') }}" class="btn-custom btn-custom-light">Batal</a>
      </div>
    </form>
  </div>
@endsection
