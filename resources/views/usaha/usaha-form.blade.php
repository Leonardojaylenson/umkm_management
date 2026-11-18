@extends('template.app')
@section('title', $business->exists ? 'Ubah Profil Usaha' : 'Buat Profil Usaha')
@section('subtitle', 'Isi identitas usaha Anda.')

@section('content')
  <div class="card border-light shadow-sm p-4 mx-auto w-100" style="max-width: 720px">
    <form action="{{ $business->exists ? route('usaha.update') : route('usaha.store') }}" method="POST" enctype="multipart/form-data">
      @csrf
      @if ($business->exists)
        @method('PUT')
      @endif

      <div class="mb-3">
        <label class="form-label-custom">Nama Usaha</label>
        <input type="text" name="business_name" class="form-control-custom" value="{{ old('business_name', $business->business_name) }}" required>
        @error('business_name')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
      </div>

      <div class="mb-3">
        <label class="form-label-custom">Jenis Usaha (contoh: Kuliner, Retail, Jasa)</label>
        <input type="text" name="business_type" class="form-control-custom" value="{{ old('business_type', $business->business_type) }}" required>
        @error('business_type')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
      </div>

      <div class="mb-3">
        <label class="form-label-custom">Alamat</label>
        <textarea name="address" rows="3" class="form-control-custom">{{ old('address', $business->address) }}</textarea>
        @error('address')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
      </div>

      <div class="mb-3">
        <label class="form-label-custom">No. Telepon</label>
        <input type="text" name="phone" class="form-control-custom" value="{{ old('phone', $business->phone) }}">
        @error('phone')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
      </div>

      <div class="mb-3">
        <label class="form-label-custom">Logo (opsional, maksimal 2 MB)</label>
        <input type="file" name="logo" accept="image/*" class="form-control-custom">
        @error('logo')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
        @if ($business->logo)
          <img src="{{ asset('storage/' . $business->logo) }}" alt="Logo" class="rounded mt-2" style="height:70px">
        @endif
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn-custom btn-custom-primary">Simpan</button>
        @if ($business->exists)
          <a href="{{ route('usaha.show') }}" class="btn-custom btn-custom-light">Batal</a>
        @endif
      </div>
    </form>
  </div>
@endsection
