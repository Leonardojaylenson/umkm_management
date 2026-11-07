@extends('template.app')
@section('title', 'Detail Pengguna')
@section('subtitle', $user->name)

@section('content')
  <div class="card border-light shadow-sm p-4" style="max-width: 640px">
    <dl class="row mb-4">
      <dt class="col-sm-4">Nama</dt><dd class="col-sm-8">{{ $user->name }}</dd>
      <dt class="col-sm-4">Email</dt><dd class="col-sm-8">{{ $user->email }}</dd>
      <dt class="col-sm-4">Telepon</dt><dd class="col-sm-8">{{ $user->phone ?: '-' }}</dd>
      <dt class="col-sm-4">Peran</dt><dd class="col-sm-8">{{ $user->role_label }}</dd>
      <dt class="col-sm-4">Usaha</dt><dd class="col-sm-8">{{ $user->business->business_name ?? '-' }}</dd>
      <dt class="col-sm-4">Status</dt><dd class="col-sm-8"><x-status :value="$user->is_active ? 'active' : 'inactive'" /></dd>
      <dt class="col-sm-4">Bergabung</dt><dd class="col-sm-8">{{ tanggal($user->created_at) }}</dd>
    </dl>
    <div class="d-flex gap-2">
      <a href="{{ route('pengguna.edit', $user) }}" class="btn-custom btn-custom-primary">Ubah</a>
      <a href="{{ route('pengguna.index') }}" class="btn-custom btn-custom-light">Kembali</a>
    </div>
  </div>
@endsection
