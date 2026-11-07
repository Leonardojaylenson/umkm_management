@extends('template.app')
@section('title', 'Daftar Pengeluaran')
@section('subtitle', 'Biaya operasional usaha per kategori.')

@section('content')
  <div class="row g-4 mb-4">
    <div class="col-12 col-md-4"><x-stat-card label="Total Pengeluaran" :value="rupiah($total)" :sub="tanggal($from) . ' s/d ' . tanggal($to)" icon="bi-wallet2" /></div>
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
            <option value="{{ $category->id_expense_category }}" @selected(request('category') == $category->id_expense_category)>{{ $category->category_name }}</option>
          @endforeach
        </select>
        <button class="btn-custom btn-custom-light btn-custom-sm">Terapkan</button>
      </div>
      <a href="{{ route('pengeluaran.create') }}" class="btn-custom btn-custom-primary btn-custom-sm"><i class="bi bi-plus-lg"></i> Catat Pengeluaran</a>
    </form>

    <div class="table-responsive">
      <table class="table-custom">
        <thead><tr><th>Tanggal</th><th>Kategori</th><th>Catatan</th><th>Jumlah</th><th class="text-center">Aksi</th></tr></thead>
        <tbody>
          @forelse ($expenses as $expense)
            <tr>
              <td>{{ tanggal($expense->expense_date) }}</td>
              <td>{{ $expense->category->category_name ?? '-' }}</td>
              <td>{{ $expense->note ?: '-' }}</td>
              <td class="table-amount">{{ rupiah($expense->amount) }}</td>
              <td>
                <div class="d-flex justify-content-center gap-1">
                  <a href="{{ route('pengeluaran.edit', $expense) }}" class="table-btn-action" title="Ubah"><i class="bi bi-pencil"></i></a>
                  <x-delete-form :action="route('pengeluaran.destroy', $expense)" message="Hapus catatan pengeluaran ini?" />
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted-green py-4">Tidak ada pengeluaran.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <x-table-footer :paginator="$expenses" />
  </div>
@endsection
