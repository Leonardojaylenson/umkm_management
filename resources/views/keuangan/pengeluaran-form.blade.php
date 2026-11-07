@extends('template.app')
@php $isNew = ! $expense->exists; @endphp
@section('title', $isNew ? 'Catat Pengeluaran' : 'Ubah Pengeluaran')
@section('subtitle', 'Bahan, gaji, sewa, listrik, dan biaya lainnya.')

@section('content')
  <div class="card border-light shadow-sm p-4" style="max-width: 640px">
    <form action="{{ $isNew ? route('pengeluaran.store') : route('pengeluaran.update', $expense) }}" method="POST">
      @csrf
      @unless ($isNew) @method('PUT') @endunless

      <x-select name="id_expense_category" label="Kategori" :options="$categories->pluck('category_name', 'id_expense_category')" :value="$expense->id_expense_category" placeholder="- Pilih kategori -" required />
      <a href="{{ route('kategori-pengeluaran.index') }}" class="small d-block mb-3">Kelola kategori pengeluaran</a>
      <x-input name="expense_date" label="Tanggal" type="date" :value="$expense->expense_date?->toDateString()" required />
      <x-input name="amount" label="Jumlah (Rp)" type="number" min="1" step="any" :value="$expense->amount" required />
      <x-input name="note" label="Catatan" :value="$expense->note" />

      <div class="d-flex gap-2">
        <button type="submit" class="btn-custom btn-custom-primary">Simpan</button>
        <a href="{{ route('pengeluaran.index') }}" class="btn-custom btn-custom-light">Batal</a>
      </div>
    </form>
  </div>
@endsection
