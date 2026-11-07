@extends('template.app')
@section('title', 'Ubah Kata Sandi')
@section('subtitle', 'Gunakan kata sandi yang sulit ditebak.')

@section('content')
  <div class="card border-light shadow-sm p-4" style="max-width: 520px">
    <form action="{{ route('profil.password.update') }}" method="POST">
      @csrf @method('PUT')
      <x-input name="current_password" label="Kata Sandi Lama" type="password" required />
      <x-input name="password" label="Kata Sandi Baru" type="password" hint="Minimal 6 karakter." required />
      <x-input name="password_confirmation" label="Ulangi Kata Sandi Baru" type="password" required />
      <div class="d-flex gap-2">
        <button type="submit" class="btn-custom btn-custom-primary">Ubah Kata Sandi</button>
        <a href="{{ route('profil') }}" class="btn-custom btn-custom-light">Batal</a>
      </div>
    </form>
  </div>
@endsection
