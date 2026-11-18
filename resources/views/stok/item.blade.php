@extends('template.app')
@section('title', 'Daftar Item')
@section('subtitle', 'Barang dan layanan yang Anda jual.')

@section('content')
  @if ($lowStockCount > 0 && request('filter') != 'stok-menipis')
    <div class="alert-custom alert-custom-warning">
      <i class="bi bi-exclamation-circle-fill alert-custom-icon"></i>
      <div class="alert-custom-content">
        <strong>{{ $lowStockCount }} item</strong> stoknya menipis.
        <a href="{{ route('item.index', ['filter' => 'stok-menipis']) }}">Lihat daftar</a>
      </div>
    </div>
  @endif

  <div class="table-card-custom">
    <form class="table-header-control" method="GET">
      <div class="table-search-box">
        <i class="bi bi-search table-search-icon"></i>
        <input type="text" name="q" value="{{ request('q') }}" class="table-search-input" placeholder="Cari nama item...">
      </div>
      <div class="table-filter-group">
        <select name="category" class="form-select-custom form-control-custom-sm" onchange="this.form.submit()">
          <option value="">Semua Kategori</option>
          @foreach ($categories as $category)
            <option value="{{ $category->id_category }}" {{ request('category') == $category->id_category ? 'selected' : '' }}>{{ $category->category_name }}</option>
          @endforeach
        </select>
        <select name="filter" class="form-select-custom form-control-custom-sm" onchange="this.form.submit()">
          <option value="">Semua Stok</option>
          <option value="stok-menipis" {{ request('filter') == 'stok-menipis' ? 'selected' : '' }}>Stok Menipis</option>
        </select>
        <a href="{{ route('kategori.index') }}" class="btn-custom btn-custom-light btn-custom-sm">Kategori</a>
        <a href="{{ route('satuan.index') }}" class="btn-custom btn-custom-light btn-custom-sm">Satuan</a>
        <a href="{{ route('item.create') }}" class="btn-custom btn-custom-primary btn-custom-sm"><i class="bi bi-plus-lg"></i> Tambah Item</a>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table-custom">
        <thead>
          <tr>
            <th>Item</th><th>Kategori</th><th>Harga Jual</th><th>Harga Modal</th><th>Stok</th>
            <th class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($items as $item)
            <tr>
              <td>
                <div class="table-user-cell">
                  @if ($item->photo)
                    <img src="{{ asset('storage/' . $item->photo) }}" alt="" class="table-user-avatar" style="object-fit:cover">
                  @else
                    <span class="table-user-avatar d-inline-flex align-items-center justify-content-center bg-light"><i class="bi bi-box-seam text-muted-green"></i></span>
                  @endif
                  <div class="table-user-name">{{ $item->item_name }}</div>
                </div>
              </td>
              <td>{{ $item->category->category_name ?? '-' }}</td>
              <td class="table-amount">{{ rupiah($item->selling_price) }}</td>
              <td>{{ rupiah($item->cost_price) }}</td>
              <td>
                @if ($item->is_service)
                  <span class="text-muted-green">Tanpa stok</span>
                @else
                  {{ qty($item->stock) }} {{ $item->unit->unit_name ?? '' }}
                  @if ($item->stock <= $item->min_stock)
                    <span class="badge-table pending ms-1">Menipis</span>
                  @endif
                @endif
              </td>
              <td>
                <div class="d-flex justify-content-center gap-1">
                  <a href="{{ route('item.show', $item->id_item) }}" class="table-btn-action" title="Detail"><i class="bi bi-eye"></i></a>
                  <a href="{{ route('item.edit', $item->id_item) }}" class="table-btn-action" title="Ubah"><i class="bi bi-pencil"></i></a>

                  <form action="{{ route('item.destroy', $item->id_item) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus item ini?')">
                    @csrf
                    @method('DELETE')
                    <button class="table-btn-action delete" title="Hapus"><i class="bi bi-trash"></i></button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted-green py-4">Tidak ada item.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="table-footer-control">{{ $items->links() }}</div>
  </div>
@endsection
