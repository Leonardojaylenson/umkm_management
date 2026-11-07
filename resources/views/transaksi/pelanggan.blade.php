@extends('template.app')
@section('title', 'Pelanggan')
@section('subtitle', 'Data pelanggan untuk transaksi dan piutang.')

@section('content')
  <div class="table-card-custom">
    <form class="table-header-control" method="GET">
      <div class="table-search-box">
        <i class="bi bi-search table-search-icon"></i>
        <input type="text" name="q" value="{{ request('q') }}" class="table-search-input" placeholder="Cari nama atau telepon...">
      </div>
      <a href="{{ route('pelanggan.create') }}" class="btn-custom btn-custom-primary btn-custom-sm"><i class="bi bi-plus-lg"></i> Tambah Pelanggan</a>
    </form>

    <div class="table-responsive">
      <table class="table-custom">
        <thead><tr><th>Nama</th><th>Telepon</th><th>Alamat</th><th>Transaksi</th><th class="text-center">Aksi</th></tr></thead>
        <tbody>
          @forelse ($customers as $customer)
            <tr>
              <td class="table-product-name">{{ $customer->customer_name }}</td>
              <td>{{ $customer->phone ?: '-' }}</td>
              <td>{{ \Illuminate\Support\Str::limit($customer->address, 40) ?: '-' }}</td>
              <td>{{ $customer->transactions_count }}</td>
              <td>
                <div class="d-flex justify-content-center gap-1">
                  <a href="{{ route('pelanggan.show', $customer) }}" class="table-btn-action" title="Detail"><i class="bi bi-eye"></i></a>
                  <a href="{{ route('pelanggan.edit', $customer) }}" class="table-btn-action" title="Ubah"><i class="bi bi-pencil"></i></a>
                  <x-delete-form :action="route('pelanggan.destroy', $customer)" :message="'Hapus pelanggan ' . $customer->customer_name . '?'" />
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted-green py-4">Belum ada pelanggan.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <x-table-footer :paginator="$customers" />
  </div>
@endsection
