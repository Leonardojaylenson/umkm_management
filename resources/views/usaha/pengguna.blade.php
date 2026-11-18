@extends('template.app')
@section('title', 'Daftar Pengguna')
@section('subtitle', 'Akun pengguna usaha Anda beserta levelnya.')

@section('content')
  <div class="table-card-custom">
    <form class="table-header-control" method="GET">
      <div class="table-search-box">
        <i class="bi bi-search table-search-icon"></i>
        <input type="text" name="q" value="{{ request('q') }}" class="table-search-input" placeholder="Cari nama atau email...">
      </div>
      <a href="{{ route('pengguna.create') }}" class="btn-custom btn-custom-primary btn-custom-sm"><i class="bi bi-plus-lg"></i> Tambah Pengguna</a>
    </form>

    <div class="table-responsive">
      <table class="table-custom">
        <thead>
          <tr><th>Nama</th><th>Telepon</th><th>Level</th><th>Status</th><th class="text-center">Aksi</th></tr>
        </thead>
        <tbody>
          @forelse ($users as $user)
            <tr>
              <td>
                <div class="table-user-name">{{ $user->name }}</div>
                <div class="table-user-sub">{{ $user->email }}</div>
              </td>
              <td>{{ $user->phone ?: '-' }}</td>
              <td>{{ $user->level->level_name ?? '-' }}</td>
              <td>{!! badge($user->is_active ? 'active' : 'inactive') !!}</td>
              <td>
                <div class="d-flex justify-content-center gap-1">
                  <a href="{{ route('pengguna.show', $user->id_user) }}" class="table-btn-action" title="Detail"><i class="bi bi-eye"></i></a>
                  <a href="{{ route('pengguna.edit', $user->id_user) }}" class="table-btn-action" title="Ubah"><i class="bi bi-pencil"></i></a>

                  @if ($user->id_user != auth()->id())
                    <form action="{{ route('pengguna.toggle', $user->id_user) }}" method="POST" class="d-inline">
                      @csrf
                      @method('PATCH')
                      <button class="table-btn-action" title="Aktifkan / nonaktifkan"><i class="bi bi-person-slash"></i></button>
                    </form>

                    <form action="{{ route('pengguna.destroy', $user->id_user) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus akun ini?')">
                      @csrf
                      @method('DELETE')
                      <button class="table-btn-action delete" title="Hapus"><i class="bi bi-trash"></i></button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted-green py-4">Belum ada pengguna.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="table-footer-control">{{ $users->links() }}</div>
  </div>
@endsection
