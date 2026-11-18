@extends('template.app')
@section('title', $receivable->exists ? 'Ubah Piutang' : 'Catat Piutang')
@section('subtitle', 'Tagihan ke pelanggan di luar transaksi penjualan.')

@section('content')
  <div class="card border-light shadow-sm p-4 mx-auto w-100" style="max-width: 640px">

    @if ($receivable->id_transaction)
      <p class="text-muted-green small">
        Piutang ini berasal dari transaksi, jadi pelanggan dan total mengikuti transaksi.
        Anda hanya bisa mengubah jatuh tempo dan catatan.
      </p>
    @endif

    <form action="{{ $receivable->exists ? route('piutang.update', $receivable->id_receivable) : route('piutang.store') }}" method="POST">
      @csrf
      @if ($receivable->exists)
        @method('PUT')
      @endif

      @if ($receivable->id_transaction)
        <input type="hidden" name="id_customer" value="{{ $receivable->id_customer }}">
        <input type="hidden" name="amount" value="{{ $receivable->amount }}">
        <input type="hidden" name="receivable_date" value="{{ $receivable->receivable_date->toDateString() }}">

        <div class="mb-3">
          <label class="form-label-custom">Pelanggan</label>
          <input type="text" class="form-control-custom" value="{{ $receivable->customer->customer_name ?? '-' }}" readonly>
        </div>

        <div class="mb-3">
          <label class="form-label-custom">Total Piutang</label>
          <input type="text" class="form-control-custom" value="{{ rupiah($receivable->amount) }}" readonly>
        </div>
      @else
        <div class="mb-3">
          <label class="form-label-custom">Pelanggan</label>
          <select name="id_customer" class="form-select-custom" required>
            <option value="">- Pilih pelanggan -</option>
            @foreach ($customers as $customer)
              <option value="{{ $customer->id_customer }}" {{ old('id_customer', $receivable->id_customer) == $customer->id_customer ? 'selected' : '' }}>{{ $customer->customer_name }}</option>
            @endforeach
          </select>
          @error('id_customer')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
          <label class="form-label-custom">Total Piutang (Rp)</label>
          <input type="number" name="amount" min="1" step="any" class="form-control-custom" value="{{ old('amount', $receivable->amount) }}" required>
          @error('amount')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
          <label class="form-label-custom">Tanggal Piutang</label>
          <input type="date" name="receivable_date" class="form-control-custom" value="{{ old('receivable_date', $receivable->receivable_date->toDateString()) }}" required>
          @error('receivable_date')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
        </div>
      @endif

      <div class="mb-3">
        <label class="form-label-custom">Jatuh Tempo (opsional)</label>
        <input type="date" name="due_date" class="form-control-custom" value="{{ old('due_date', $receivable->due_date ? $receivable->due_date->toDateString() : '') }}">
        @error('due_date')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
      </div>

      <div class="mb-3">
        <label class="form-label-custom">Catatan</label>
        <input type="text" name="note" class="form-control-custom" value="{{ old('note', $receivable->note) }}">
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn-custom btn-custom-primary">Simpan</button>
        <a href="{{ route('piutang.index') }}" class="btn-custom btn-custom-light">Batal</a>
      </div>
    </form>
  </div>
@endsection
