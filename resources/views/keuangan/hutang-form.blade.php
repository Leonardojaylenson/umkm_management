@extends('template.app')
@section('title', $debt->exists ? 'Ubah Hutang' : 'Catat Hutang')
@section('subtitle', 'Hutang usaha ke supplier.')

@section('content')
  <div class="card border-light shadow-sm p-4 mx-auto w-100" style="max-width: 640px">
    <form action="{{ $debt->exists ? route('hutang.update', $debt->id_debt) : route('hutang.store') }}" method="POST">
      @csrf
      @if ($debt->exists)
        @method('PUT')
      @endif

      <div class="mb-3">
        <label class="form-label-custom">Supplier</label>
        <select name="id_supplier" class="form-select-custom" required>
          <option value="">- Pilih supplier -</option>
          @foreach ($suppliers as $supplier)
            <option value="{{ $supplier->id_supplier }}" {{ old('id_supplier', $debt->id_supplier) == $supplier->id_supplier ? 'selected' : '' }}>{{ $supplier->supplier_name }}</option>
          @endforeach
        </select>
        @error('id_supplier')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
        <a href="{{ route('supplier.create') }}" class="small">+ Tambah supplier baru</a>
      </div>

      <div class="mb-3">
        <label class="form-label-custom">Total Hutang (Rp)</label>
        <input type="number" name="amount" min="1" step="any" class="form-control-custom" value="{{ old('amount', $debt->amount) }}" required>
        @error('amount')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
      </div>

      <div class="mb-3">
        <label class="form-label-custom">Tanggal Hutang</label>
        <input type="date" name="debt_date" class="form-control-custom" value="{{ old('debt_date', $debt->debt_date->toDateString()) }}" required>
        @error('debt_date')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
      </div>

      <div class="mb-3">
        <label class="form-label-custom">Jatuh Tempo (opsional)</label>
        <input type="date" name="due_date" class="form-control-custom" value="{{ old('due_date', $debt->due_date ? $debt->due_date->toDateString() : '') }}">
        @error('due_date')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
      </div>

      <div class="mb-3">
        <label class="form-label-custom">Catatan</label>
        <input type="text" name="note" class="form-control-custom" value="{{ old('note', $debt->note) }}">
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn-custom btn-custom-primary">Simpan</button>
        <a href="{{ route('hutang.index') }}" class="btn-custom btn-custom-light">Batal</a>
      </div>
    </form>
  </div>
@endsection
