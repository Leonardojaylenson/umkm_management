@extends('template.app')
@php $isNew = ! $user->exists; @endphp
@section('title', $isNew ? 'Tambah Karyawan' : 'Ubah Pengguna')
@section('subtitle', $isNew ? 'Buat akun baru untuk karyawan Anda.' : 'Perbarui data & peran pengguna.')

@section('content')
  <div class="card border-light shadow-sm p-4" style="max-width: 640px">
    <form action="{{ $isNew ? route('pengguna.store') : route('pengguna.update', $user) }}" method="POST">
      @csrf
      @unless ($isNew) @method('PUT') @endunless

      <x-input name="name" label="Nama" :value="$user->name" required />
      <x-input name="email" label="Email" type="email" :value="$user->email" required />
      <x-input name="phone" label="No. Telepon" :value="$user->phone" />

      @if ($user->is(auth()->user()))
        <p class="text-muted-green small">Peran akun Anda sendiri tidak bisa diubah.</p>
      @else
        <x-select name="role" label="Peran" :options="['cashier' => 'Kasir', 'owner' => 'Pemilik']" :value="$user->role" required />
      @endif

      <x-input name="password" label="Kata Sandi {{ $isNew ? '' : '(kosongkan jika tidak diubah)' }}" type="password" :required="$isNew" />
      <x-input name="password_confirmation" label="Ulangi Kata Sandi" type="password" :required="$isNew" />

      <div class="d-flex gap-2">
        <button type="submit" class="btn-custom btn-custom-primary">Simpan</button>
        <a href="{{ route('pengguna.index') }}" class="btn-custom btn-custom-light">Batal</a>
      </div>
    </form>
  </div>
@endsection
