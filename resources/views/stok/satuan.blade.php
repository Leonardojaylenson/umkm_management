@extends('template.app')
@section('title', 'Satuan Item')
@section('subtitle', 'Satuan jual: pcs, kg, porsi, jam, dan sebagainya.')

@section('content')
  <div class="row g-4">

    <div class="col-12 col-lg-4">
      <div class="card border-light shadow-sm p-4">
        <h5 class="card-title mb-3">{{ $editing ? 'Ubah' : 'Tambah' }} Satuan</h5>

        <form action="{{ $editing ? route('satuan.update', $editing->id_unit) : route('satuan.store') }}" method="POST">
          @csrf
          @if ($editing)
            @method('PUT')
          @endif

          <div class="mb-3">
            <label class="form-label-custom">Nama Satuan</label>
            <input type="text" name="unit_name" class="form-control-custom" value="{{ old('unit_name', $editing->unit_name ?? '') }}" required>
            @error('unit_name')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn-custom btn-custom-primary">Simpan</button>
            @if ($editing)
              <a href="{{ route('satuan.index') }}" class="btn-custom btn-custom-light">Batal</a>
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
              <tr><th>Nama</th><th>Jumlah Item</th><th class="text-center">Aksi</th></tr>
            </thead>
            <tbody>
              @forelse ($rows as $row)
                <tr>
                  <td class="table-product-name">{{ $row->unit_name }}</td>
                  <td>{{ $row->items_count }}</td>
                  <td>
                    <div class="d-flex justify-content-center gap-1">
                      <a href="{{ route('satuan.edit', $row->id_unit) }}" class="table-btn-action" title="Ubah"><i class="bi bi-pencil"></i></a>

                      <form action="{{ route('satuan.destroy', $row->id_unit) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus satuan ini?')">
                        @csrf
                        @method('DELETE')
                        <button class="table-btn-action delete" title="Hapus"><i class="bi bi-trash"></i></button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr><td colspan="3" class="text-center text-muted-green py-4">Belum ada satuan.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
@endsection
