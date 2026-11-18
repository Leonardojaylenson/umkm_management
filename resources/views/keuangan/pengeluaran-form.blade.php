@extends('template.app')
@section('title', $expense->exists ? 'Ubah Pengeluaran' : 'Catat Pengeluaran')
@section('subtitle', 'Bahan, gaji, sewa, listrik, dan biaya lainnya.')

@section('content')
  <div class="card border-light shadow-sm p-4 mx-auto w-100" style="max-width: 640px">
    <form action="{{ $expense->exists ? route('pengeluaran.update', $expense->id_expense) : route('pengeluaran.store') }}" method="POST">
      @csrf
      @if ($expense->exists)
        @method('PUT')
      @endif

      <div class="mb-3">
        <label class="form-label-custom">Kategori</label>
        <select name="id_expense_category" class="form-select-custom" required>
          <option value="">- Pilih kategori -</option>
          @foreach ($categories as $category)
            <option value="{{ $category->id_expense_category }}" {{ old('id_expense_category', $expense->id_expense_category) == $category->id_expense_category ? 'selected' : '' }}>{{ $category->category_name }}</option>
          @endforeach
        </select>
        @error('id_expense_category')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
        <a href="{{ route('kategori-pengeluaran.index') }}" class="small">Kelola kategori pengeluaran</a>
      </div>

      <div class="mb-3">
        <label class="form-label-custom">Tanggal</label>
        <input type="date" name="expense_date" class="form-control-custom" value="{{ old('expense_date', $expense->expense_date->toDateString()) }}" required>
        @error('expense_date')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
      </div>

      <div class="mb-3">
        <label class="form-label-custom">Jumlah (Rp)</label>
        <input type="number" name="amount" min="1" step="any" class="form-control-custom" value="{{ old('amount', $expense->amount) }}" required>
        @error('amount')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
      </div>

      <div class="mb-3">
        <label class="form-label-custom">Catatan</label>
        <input type="text" name="note" class="form-control-custom" value="{{ old('note', $expense->note) }}">
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn-custom btn-custom-primary">Simpan</button>
        <a href="{{ route('pengeluaran.index') }}" class="btn-custom btn-custom-light">Batal</a>
      </div>
    </form>
  </div>
@endsection
