@extends('template.auth')
@section('title', 'Daftar')
@section('subtitle', 'Buat akun pemilik usaha. Profil usaha diisi di langkah berikutnya.')

@section('content')
  <form action="{{ route('register') }}" method="POST" novalidate>
    @csrf

    @foreach ([
      ['name', 'Nama Lengkap', 'text', 'bi-person'],
      ['email', 'Email', 'email', 'bi-envelope'],
      ['phone', 'No. Telepon (opsional)', 'text', 'bi-telephone'],
      ['password', 'Kata Sandi (min. 6 karakter)', 'password', 'bi-shield-lock'],
      ['password_confirmation', 'Ulangi Kata Sandi', 'password', 'bi-shield-check'],
    ] as [$name, $label, $type, $icon])
      <div class="login-form-group">
        <label for="{{ $name }}" class="login-form-label">{{ $label }}</label>
        <div class="login-input-group">
          <i class="bi {{ $icon }} input-icon"></i>
          <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}" class="login-input"
            value="{{ $type === 'password' ? '' : old($name) }}" @required($name !== 'phone')>
        </div>
        @error($name)<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
      </div>
    @endforeach

    <button type="submit" class="btn-login mt-3">
      <span>Buat Akun</span><i class="bi bi-arrow-right"></i>
    </button>
  </form>

  <p class="login-footer-text">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
@endsection
