@extends('template.app')
@section('title', 'Daftar Pengguna')
@section('subtitle', 'Akun pemilik dan karyawan (kasir) usaha Anda.')

@section('content')
  <div class="table-card-custom">
    <form class="table-header-control" method="GET">
      <div class="table-search-box">
        <i class="bi bi-search table-search-icon"></i>
        <input type="text" name="q" value="{{ request('q') }}" class="table-search-input" placeholder="Cari nama atau email...">
      </div>
      <a href="{{ route('pengguna.create') }}" class="btn-custom btn-custom-primary btn-custom-sm"><i class="bi bi-plus-lg"></i> Tambah Karyawan</a>
    </form>

    <div class="table-responsive">
      <table class="table-custom">
        <thead>
          <tr><th>Nama</th><th>Telepon</th><th>Peran</th><th>Status</th><th class="text-center">Aksi</th></tr>
        </thead>
        <tbody>
          @forelse ($users as $user)
            <tr>
              <td>
                <div class="table-user-name">{{ $user->name }} @if ($user->is(auth()->user()))<small class="text-muted-green">(Anda)</small>@endif</div>
                <div class="table-user-sub">{{ $user->email }}</div>
              </td>
              <td>{{ $user->phone ?: '-' }}</td>
              <td>{{ $user->role_label }}</td>
              <td><x-status :value="$user->is_active ? 'active' : 'inactive'" /></td>
              <td>
                <div class="d-flex justify-content-center gap-1">
                  <a href="{{ route('pengguna.show', $user) }}" class="table-btn-action" title="Detail"><i class="bi bi-eye"></i></a>
                  <a href="{{ route('pengguna.edit', $user) }}" class="table-btn-action" title="Ubah"><i class="bi bi-pencil"></i></a>
                  @unless ($user->is(auth()->user()))
                    <form action="{{ route('pengguna.toggle', $user) }}" method="POST" class="d-inline">
                      @csrf @method('PATCH')
                      <button class="table-btn-action" title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                        <i class="bi {{ $user->is_active ? 'bi-person-slash' : 'bi-person-check' }}"></i>
                      </button>
                    </form>
                    <x-delete-form :action="route('pengguna.destroy', $user)" :message="'Hapus akun ' . $user->name . '?'" />
                  @endunless
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted-green py-4">Belum ada pengguna.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <x-table-footer :paginator="$users" />
  </div>
@endsection
