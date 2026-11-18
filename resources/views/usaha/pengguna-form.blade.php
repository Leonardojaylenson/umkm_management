@extends('template.app')
@section('title', $user->exists ? 'Ubah Pengguna' : 'Tambah Pengguna')
@section('subtitle', 'Pilih level untuk menentukan menu yang boleh dibuka.')

@section('content')
  <div class="card border-light shadow-sm p-4 mx-auto w-100" style="max-width: 640px">
    <form action="{{ $user->exists ? route('pengguna.update', $user->id_user) : route('pengguna.store') }}" method="POST">
      @csrf
      @if ($user->exists)
        @method('PUT')
      @endif

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

      @if ($user->id_user == auth()->id())
        <p class="text-muted-green small">Level akun Anda sendiri tidak bisa diubah.</p>
        <input type="hidden" name="id_level" value="{{ $user->id_level }}">
      @else
        <div class="mb-3">
          <label class="form-label-custom">Level</label>
          <select name="id_level" class="form-select-custom" required>
            <option value="">- Pilih level -</option>
            @foreach ($levels as $level)
              <option value="{{ $level->id_level }}" {{ old('id_level', $user->id_level) == $level->id_level ? 'selected' : '' }}>{{ $level->level_name }}</option>
            @endforeach
          </select>
          @error('id_level')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
          <a href="{{ route('level.index') }}" class="small">Atur level &amp; hak akses</a>
        </div>
      @endif

      <div class="mb-3">
        <label class="form-label-custom">Kata Sandi {{ $user->exists ? '(kosongkan jika tidak diubah)' : '' }}</label>
        <input type="password" name="password" class="form-control-custom" {{ $user->exists ? '' : 'required' }}>
        @error('password')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
      </div>

      <div class="mb-3">
        <label class="form-label-custom">Ulangi Kata Sandi</label>
        <input type="password" name="password_confirmation" class="form-control-custom" {{ $user->exists ? '' : 'required' }}>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn-custom btn-custom-primary">Simpan</button>
        <a href="{{ route('pengguna.index') }}" class="btn-custom btn-custom-light">Batal</a>
      </div>
    </form>
  </div>
@endsection
