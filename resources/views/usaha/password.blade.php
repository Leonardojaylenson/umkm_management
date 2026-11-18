@extends('template.app')
@section('title', 'Ubah Kata Sandi')
@section('subtitle', 'Gunakan kata sandi yang sulit ditebak.')

@section('content')
  <div class="card border-light shadow-sm p-4 mx-auto w-100" style="max-width: 520px">
    <form action="{{ route('profil.password.update') }}" method="POST">
      @csrf
      @method('PUT')

      <div class="mb-3">
        <label class="form-label-custom">Kata Sandi Lama</label>
        <input type="password" name="current_password" class="form-control-custom" required>
        @error('current_password')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
      </div>

      <div class="mb-3">
        <label class="form-label-custom">Kata Sandi Baru (minimal 6 karakter)</label>
        <input type="password" name="password" class="form-control-custom" required>
        @error('password')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
      </div>

      <div class="mb-3">
        <label class="form-label-custom">Ulangi Kata Sandi Baru</label>
        <input type="password" name="password_confirmation" class="form-control-custom" required>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn-custom btn-custom-primary">Ubah Kata Sandi</button>
        <a href="{{ route('profil') }}" class="btn-custom btn-custom-light">Batal</a>
      </div>
    </form>
  </div>
@endsection
