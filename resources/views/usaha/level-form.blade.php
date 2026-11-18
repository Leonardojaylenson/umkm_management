@extends('template.app')
@section('title', 'Atur Level')
@section('subtitle', $level->level_name)

@section('content')
  @php
    $menuDipilih = old('menu', $level->menus->pluck('menu')->all());
  @endphp

  <div class="card border-light shadow-sm p-4 mx-auto w-100" style="max-width: 640px">
    <form action="{{ route('level.update', $level->id_level) }}" method="POST">
      @csrf
      @method('PUT')

      <div class="mb-3">
        <label class="form-label-custom">Nama Level</label>
        <input type="text" name="level_name" class="form-control-custom" value="{{ old('level_name', $level->level_name) }}" required>
        @error('level_name')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
      </div>

      <label class="form-label-custom">Menu yang boleh dibuka</label>
      <p class="text-muted-green small">Dashboard selalu bisa dibuka oleh semua level.</p>

      @foreach ($menus as $kode => $menu)
        <div class="form-check-custom mb-2">
          <input type="checkbox" class="form-check-input-custom" id="menu-{{ $kode }}" name="menu[]" value="{{ $kode }}"
            {{ in_array($kode, $menuDipilih) ? 'checked' : '' }}>
          <label for="menu-{{ $kode }}" class="form-label-custom mb-0">{{ $menu['nama'] }}</label>
        </div>
      @endforeach

      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn-custom btn-custom-primary">Simpan</button>
        <a href="{{ route('level.index') }}" class="btn-custom btn-custom-light">Batal</a>
      </div>
    </form>
  </div>
@endsection
