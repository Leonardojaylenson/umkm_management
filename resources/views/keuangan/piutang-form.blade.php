@extends('template.app')
@php $isNew = ! $receivable->exists; $fromTrx = (bool) $receivable->id_transaction; @endphp
@section('title', $isNew ? 'Catat Piutang' : 'Ubah Piutang')
@section('subtitle', 'Tagihan ke pelanggan di luar transaksi penjualan.')

@section('content')
  <div class="card border-light shadow-sm p-4" style="max-width: 640px">
    @if ($fromTrx)
      <p class="text-muted-green small">Piutang ini berasal dari transaksi, jadi pelanggan dan total mengikuti transaksi. Anda hanya bisa mengubah jatuh tempo dan catatan.</p>
    @endif

    <form action="{{ $isNew ? route('piutang.store') : route('piutang.update', $receivable) }}" method="POST">
      @csrf
      @unless ($isNew) @method('PUT') @endunless

            <x-select name="id_customer" label="Pelanggan" :options="$customers->pluck('customer_name', 'id_customer')" :value="$receivable->id_customer" placeholder="- Pilih pelanggan -" :disabled="$fromTrx" required />
      <x-input name="amount" label="Total Piutang (Rp)" type="number" min="1" step="any" :value="$receivable->amount" :disabled="$fromTrx" required />
      <x-input name="receivable_date" label="Tanggal Piutang" type="date" :value="$receivable->receivable_date?->toDateString()" :disabled="$fromTrx" required />
      @if ($fromTrx)
                <input type="hidden" name="id_customer" value="{{ $receivable->id_customer }}">
        <input type="hidden" name="amount" value="{{ $receivable->amount }}">
        <input type="hidden" name="receivable_date" value="{{ $receivable->receivable_date->toDateString() }}">
      @endif
      <x-input name="due_date" label="Jatuh Tempo (opsional)" type="date" :value="$receivable->due_date?->toDateString()" />
      <x-input name="note" label="Catatan" :value="$receivable->note" />

      <div class="d-flex gap-2">
        <button type="submit" class="btn-custom btn-custom-primary">Simpan</button>
        <a href="{{ route('piutang.index') }}" class="btn-custom btn-custom-light">Batal</a>
      </div>
    </form>
  </div>
@endsection
