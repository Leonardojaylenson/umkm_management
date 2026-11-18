@extends('template.app')
@section('title', 'Profil Saya')
@section('subtitle', 'Data akun yang sedang Anda pakai.')

@section('content')
  <div class="row g-4">
    <div class="col-12 col-lg-6">
      <div class="card border-light shadow-sm p-4">
        <h5 class="card-title mb-4">Data Akun</h5>

        <form action="{{ route('profil.update') }}" method="POST">
          @csrf
          @method('PUT')

          <div class="mb-3">
            <label class="form-label-custom">Nama</label>
            <input type="text" name="name" class="form-control-custom" value="{{ old('name', $user->name) }}" required>
            @error('name')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
          </div>

          <div class="mb-3">
            <label class="form-label-custom">Email</label>
            <input type="email" name="email" class="form-control-custom" value="{{ old('email', $user->email) }}" required>
            @error('email')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
          </div>

          <div class="mb-3">
            <label class="form-label-custom">No. Telepon</label>
            <input type="text" name="phone" class="form-control-custom" value="{{ old('phone', $user->phone) }}">
            @error('phone')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
          </div>

          <button type="submit" class="btn-custom btn-custom-primary">Simpan Perubahan</button>
        </form>
      </div>
    </div>

    <div class="col-12 col-lg-6">
      <div class="card border-light shadow-sm p-4 h-100">
        <h5 class="card-title mb-4">Informasi</h5>
        <dl class="row mb-0">
          <dt class="col-5">Level</dt><dd class="col-7">{{ $user->level->level_name ?? '-' }}</dd>
          <dt class="col-5">Usaha</dt><dd class="col-7">{{ $user->business->business_name ?? '-' }}</dd>
          <dt class="col-5">Status</dt><dd class="col-7">{!! badge($user->is_active ? 'active' : 'inactive') !!}</dd>
        </dl>
        <a href="{{ route('profil.password') }}" class="btn-custom btn-custom-outline-primary mt-4 align-self-start">
          <i class="bi bi-lock"></i> Ubah Kata Sandi
        </a>
      </div>
    </div>
  </div>
@endsection
