@props(['base', 'field', 'rows', 'editing' => null, 'usage', 'usageLabel', 'label'])

<div class="row g-4">
  <div class="col-12 col-lg-4">
    <div class="card border-light shadow-sm p-4">
      <h5 class="card-title mb-3">{{ $editing ? 'Ubah' : 'Tambah' }} {{ $label }}</h5>

      <form action="{{ $editing ? route($base . '.update', $editing) : route($base . '.store') }}" method="POST">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-input :name="$field" :label="'Nama ' . $label" :value="$editing?->{$field}" required autofocus />

        <div class="d-flex gap-2">
          <button type="submit" class="btn-custom btn-custom-primary">Simpan</button>
          @if ($editing)
            <a href="{{ route($base . '.index') }}" class="btn-custom btn-custom-light">Batal</a>
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
            <tr>
              <th>Nama</th>
              <th>{{ $usageLabel }}</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($rows as $row)
              <tr>
                <td class="table-product-name">{{ $row->{$field} }}</td>
                <td>{{ $row->{$usage} }}</td>
                <td>
                  <div class="d-flex justify-content-center gap-1">
                    <a href="{{ route($base . '.edit', $row) }}" class="table-btn-action" title="Ubah"><i class="bi bi-pencil"></i></a>
                    <x-delete-form :action="route($base . '.destroy', $row)" :message="'Hapus ' . $row->{$field} . '?'" />
                  </div>
                </td>
              </tr>
            @empty
              <tr><td colspan="3" class="text-center text-muted-green py-4">Belum ada data.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
