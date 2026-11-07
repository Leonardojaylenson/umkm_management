@extends('template.app')
@php $isNew = ! $debt->exists; @endphp
@section('title', $isNew ? 'Catat Hutang' : 'Ubah Hutang')
@section('subtitle', 'Hutang usaha ke supplier.')

@section('content')
  <div class="card border-light shadow-sm p-4" style="max-width: 640px">
    <form action="{{ $isNew ? route('hutang.store') : route('hutang.update', $debt) }}" method="POST">
      @csrf
      @unless ($isNew) @method('PUT') @endunless

      <x-select name="id_supplier" label="Supplier" :options="$suppliers->pluck('supplier_name', 'id_supplier')" :value="$debt->id_supplier" placeholder="- Pilih supplier -" required />
      <a href="{{ route('supplier.create') }}" class="small d-block mb-3">+ Tambah supplier baru</a>
      <x-input name="amount" label="Total Hutang (Rp)" type="number" min="1" step="any" :value="$debt->amount" required />
      <x-input name="debt_date" label="Tanggal Hutang" type="date" :value="$debt->debt_date?->toDateString()" required />
      <x-input name="due_date" label="Jatuh Tempo (opsional)" type="date" :value="$debt->due_date?->toDateString()" />
      <x-input name="note" label="Catatan" :value="$debt->note" />

      <div class="d-flex gap-2">
        <button type="submit" class="btn-custom btn-custom-primary">Simpan</button>
        <a href="{{ route('hutang.index') }}" class="btn-custom btn-custom-light">Batal</a>
      </div>
    </form>
  </div>
@endsection
