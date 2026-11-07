@extends('template.app')
@php $isNew = ! $business->exists; @endphp
@section('title', $isNew ? 'Buat Profil Usaha' : 'Ubah Profil Usaha')
@section('subtitle', $isNew ? 'Isi data usaha Anda untuk mulai memakai aplikasi.' : 'Perbarui identitas usaha.')

@section('content')
  <div class="card border-light shadow-sm p-4" style="max-width: 720px">
    <form action="{{ $isNew ? route('usaha.store') : route('usaha.update') }}" method="POST" enctype="multipart/form-data">
      @csrf
      @unless ($isNew) @method('PUT') @endunless

      <x-input name="business_name" label="Nama Usaha" :value="$business->business_name" required />
      <x-input name="business_type" label="Jenis Usaha" :value="$business->business_type" hint="Contoh: Kuliner, Retail, Jasa." required />
      <x-textarea name="address" label="Alamat" :value="$business->address" />
      <x-input name="phone" label="No. Telepon" :value="$business->phone" />

      <div class="mb-3">
        <label for="logo" class="form-label-custom">Logo (opsional, maks. 2 MB)</label>
        <input type="file" id="logo" name="logo" accept="image/*" class="form-control-custom">
        @error('logo')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
        @if ($business->logo)
          <img src="{{ asset('storage/' . $business->logo) }}" alt="Logo" class="rounded mt-2" style="height:70px">
        @endif
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn-custom btn-custom-primary">Simpan</button>
        @unless ($isNew)<a href="{{ route('usaha.show') }}" class="btn-custom btn-custom-light">Batal</a>@endunless
      </div>
    </form>
  </div>
@endsection
