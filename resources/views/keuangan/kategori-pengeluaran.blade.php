@extends('template.app')
@section('title', 'Kategori Pengeluaran')
@section('subtitle', 'Contoh: Bahan, Gaji, Sewa, Listrik.')

@section('content')
  <div class="row g-4">

    <div class="col-12 col-lg-4">
      <div class="card border-light shadow-sm p-4">
        <h5 class="card-title mb-3">{{ $editing ? 'Ubah' : 'Tambah' }} Kategori</h5>

        <form action="{{ $editing ? route('kategori-pengeluaran.update', $editing->id_expense_category) : route('kategori-pengeluaran.store') }}" method="POST">
          @csrf
          @if ($editing)
            @method('PUT')
          @endif

          <div class="mb-3">
            <label class="form-label-custom">Nama Kategori</label>
            <input type="text" name="category_name" class="form-control-custom" value="{{ old('category_name', $editing->category_name ?? '') }}" required>
            @error('category_name')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn-custom btn-custom-primary">Simpan</button>
            @if ($editing)
              <a href="{{ route('kategori-pengeluaran.index') }}" class="btn-custom btn-custom-light">Batal</a>
            @endif
          </div>
        </form>
      </div>
    </div>

    <div class="col-12 col-lg-8">
      <div class="table-card-custom">
        <div class="table-responsive">
          <table class="table-custom">
            <thead>
              <tr><th>Nama</th><th>Jumlah Catatan</th><th class="text-center">Aksi</th></tr>
            </thead>
            <tbody>
              @forelse ($rows as $row)
                <tr>
                  <td class="table-product-name">{{ $row->category_name }}</td>
                  <td>{{ $row->expenses_count }}</td>
                  <td>
                    <div class="d-flex justify-content-center gap-1">
                      <a href="{{ route('kategori-pengeluaran.edit', $row->id_expense_category) }}" class="table-btn-action" title="Ubah"><i class="bi bi-pencil"></i></a>

                      <form action="{{ route('kategori-pengeluaran.destroy', $row->id_expense_category) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus kategori ini?')">
                        @csrf
                        @method('DELETE')
                        <button class="table-btn-action delete" title="Hapus"><i class="bi bi-trash"></i></button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr><td colspan="3" class="text-center text-muted-green py-4">Belum ada kategori.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
@endsection
