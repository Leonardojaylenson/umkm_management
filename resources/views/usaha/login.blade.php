@extends('template.auth')
@section('title', 'Masuk')
@section('subtitle', 'Masuk untuk mengelola usaha Anda')

@section('content')
  <form action="{{ route('login') }}" method="POST" novalidate>
    @csrf

    <div class="login-form-group">
      <label for="email" class="login-form-label">Email</label>
      <div class="login-input-group">
        <i class="bi bi-envelope input-icon"></i>
        <input type="email" id="email" name="email" class="login-input" value="{{ old('email') }}" placeholder="nama@email.com" required autofocus>
      </div>
      @error('email')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
    </div>

    <div class="login-form-group">
      <label for="password" class="login-form-label">Kata Sandi</label>
      <div class="login-input-group">
        <i class="bi bi-shield-lock input-icon"></i>
        <input type="password" id="password" name="password" class="login-input login-input-password" placeholder="••••••••" required>
        <button type="button" class="password-toggle-btn" id="toggle-password" aria-label="Tampilkan kata sandi"><i class="bi bi-eye"></i></button>
      </div>
    </div>

    <button type="submit" class="btn-login mt-3">
      <span>Masuk</span><i class="bi bi-arrow-right"></i>
    </button>
  </form>

  <p class="login-footer-text">Belum punya akun? <a href="{{ route('register') }}">Daftar sebagai pemilik usaha</a></p>
@endsection
