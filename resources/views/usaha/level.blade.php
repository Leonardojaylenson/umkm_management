@extends('template.app')
@section('title', 'Pengaturan Level')
@section('subtitle', 'Atur menu apa saja yang boleh dibuka oleh tiap level pengguna.')

@section('content')
  <div class="row g-4">

    <div class="col-12 col-lg-4">
      <div class="card border-light shadow-sm p-4">
        <h5 class="card-title mb-3">Tambah Level</h5>

        <form action="{{ route('level.store') }}" method="POST">
          @csrf

          <div class="mb-3">
            <label class="form-label-custom">Nama Level</label>
            <input type="text" name="level_name" class="form-control-custom" value="{{ old('level_name') }}" required>
            <small class="text-muted">Contoh: Kasir, Admin Gudang, Manajer.</small>
            @error('level_name')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
          </div>

          <button type="submit" class="btn-custom btn-custom-primary">Tambah</button>
        </form>
      </div>
    </div>

    <div class="col-12 col-lg-8">
      <div class="table-card-custom">
        <div class="table-responsive">
          <table class="table-custom">
            <thead>
              <tr><th>Level</th><th>Menu yang boleh dibuka</th><th>Pengguna</th><th class="text-center">Aksi</th></tr>
            </thead>
            <tbody>
              @foreach ($levels as $level)
                @php
                  $namaMenu = ['Dashboard'];
                  foreach ($level->menus as $menu) {
                    $namaMenu[] = config('menu.bisa_diatur.' . $menu->menu . '.nama');
                  }
                @endphp
                <tr>
                  <td class="table-product-name">{{ $level->level_name }}</td>
                  <td>{{ $level->is_owner ? 'Semua menu' : implode(', ', $namaMenu) }}</td>
                  <td>{{ $level->users_count }}</td>
                  <td>
                    <div class="d-flex justify-content-center gap-1">
                      @if (! $level->is_owner)
                        <a href="{{ route('level.edit', $level->id_level) }}" class="table-btn-action" title="Atur menu"><i class="bi bi-pencil"></i></a>

                        <form action="{{ route('level.destroy', $level->id_level) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus level ini?')">
                          @csrf
                          @method('DELETE')
                          <button class="table-btn-action delete" title="Hapus"><i class="bi bi-trash"></i></button>
                        </form>
                      @endif
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
@endsection
