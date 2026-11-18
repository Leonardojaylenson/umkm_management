@extends('template.app')
@section('title', 'Daftar Pengeluaran')
@section('subtitle', 'Biaya operasional usaha per kategori.')

@section('content')
  <div class="row g-4 mb-4">
    <div class="col-12 col-md-4">
      <div class="card card-stat h-100">
        <div class="card-header">
          <span class="stat-label">Total Pengeluaran</span>
          <i class="bi bi-wallet2 text-muted-green fs-5"></i>
        </div>
        <div class="stat-value">{{ rupiah($total) }}</div>
        <div class="text-muted-green small mt-1">{{ tanggal($from) }} s/d {{ tanggal($to) }}</div>
      </div>
    </div>

    <div class="col-12 col-md-8">
      <div class="card border-light shadow-sm p-3 h-100">
        <div class="stat-label mb-2">Per Kategori</div>
        <div class="d-flex flex-wrap gap-2">
          @forelse ($perCategory as $row)
            <span class="badge-table pending">{{ $row->category->category_name ?? '-' }}: {{ rupiah($row->total) }}</span>
          @empty
            <span class="text-muted-green">Belum ada pengeluaran pada periode ini.</span>
          @endforelse
        </div>
      </div>
    </div>
  </div>

  <div class="table-card-custom">
    <form class="table-header-control" method="GET">
      <div class="table-filter-group">
        <input type="date" name="from" value="{{ $from }}" class="form-control-custom form-control-custom-sm">
        <input type="date" name="to" value="{{ $to }}" class="form-control-custom form-control-custom-sm">
        <select name="category" class="form-select-custom form-control-custom-sm">
          <option value="">Semua Kategori</option>
          @foreach ($categories as $category)
            <option value="{{ $category->id_expense_category }}" {{ request('category') == $category->id_expense_category ? 'selected' : '' }}>{{ $category->category_name }}</option>
          @endforeach
        </select>
        <button class="btn-custom btn-custom-light btn-custom-sm">Terapkan</button>
        <a href="{{ route('kategori-pengeluaran.index') }}" class="btn-custom btn-custom-light btn-custom-sm">Kategori</a>
      </div>
      <a href="{{ route('pengeluaran.create') }}" class="btn-custom btn-custom-primary btn-custom-sm"><i class="bi bi-plus-lg"></i> Catat Pengeluaran</a>
    </form>

    <div class="table-responsive">
      <table class="table-custom">
        <thead>
          <tr><th>Tanggal</th><th>Kategori</th><th>Catatan</th><th>Jumlah</th><th class="text-center">Aksi</th></tr>
        </thead>
        <tbody>
          @forelse ($expenses as $expense)
            <tr>
              <td>{{ tanggal($expense->expense_date) }}</td>
              <td>{{ $expense->category->category_name ?? '-' }}</td>
              <td>{{ $expense->note ?: '-' }}</td>
              <td class="table-amount">{{ rupiah($expense->amount) }}</td>
              <td>
                <div class="d-flex justify-content-center gap-1">
                  <a href="{{ route('pengeluaran.edit', $expense->id_expense) }}" class="table-btn-action" title="Ubah"><i class="bi bi-pencil"></i></a>

                  <form action="{{ route('pengeluaran.destroy', $expense->id_expense) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus catatan ini?')">
                    @csrf
                    @method('DELETE')
                    <button class="table-btn-action delete" title="Hapus"><i class="bi bi-trash"></i></button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted-green py-4">Tidak ada pengeluaran.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="table-footer-control">{{ $expenses->links() }}</div>
  </div>
@endsection
