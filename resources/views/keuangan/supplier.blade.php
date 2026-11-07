@extends('template.app')
@section('title', 'Data Supplier')
@section('subtitle', 'Pihak tempat usaha Anda berhutang.')

@section('content')
  <div class="table-card-custom">
    <form class="table-header-control" method="GET">
      <div class="table-search-box">
        <i class="bi bi-search table-search-icon"></i>
        <input type="text" name="q" value="{{ request('q') }}" class="table-search-input" placeholder="Cari supplier...">
      </div>
      <a href="{{ route('supplier.create') }}" class="btn-custom btn-custom-primary btn-custom-sm"><i class="bi bi-plus-lg"></i> Tambah Supplier</a>
    </form>

    <div class="table-responsive">
      <table class="table-custom">
        <thead><tr><th>Nama</th><th>Telepon</th><th>Alamat</th><th>Jumlah Hutang</th><th class="text-center">Aksi</th></tr></thead>
        <tbody>
          @forelse ($suppliers as $supplier)
            <tr>
              <td class="table-product-name">{{ $supplier->supplier_name }}</td>
              <td>{{ $supplier->phone ?: '-' }}</td>
              <td>{{ \Illuminate\Support\Str::limit($supplier->address, 40) ?: '-' }}</td>
              <td>{{ $supplier->debts_count }}</td>
              <td>
                <div class="d-flex justify-content-center gap-1">
                  <a href="{{ route('supplier.edit', $supplier) }}" class="table-btn-action" title="Ubah"><i class="bi bi-pencil"></i></a>
                  <x-delete-form :action="route('supplier.destroy', $supplier)" :message="'Hapus supplier ' . $supplier->supplier_name . '?'" />
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted-green py-4">Belum ada supplier.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <x-table-footer :paginator="$suppliers" />
  </div>
@endsection
