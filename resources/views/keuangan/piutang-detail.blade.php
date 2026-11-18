@extends('template.app')
@section('title', 'Detail Piutang')
@section('subtitle', $receivable->customer->customer_name ?? '')

@section('content')
  @php
    $sisa = $receivable->amount - $receivable->paid_amount;
  @endphp

  <div class="row g-4">

    <div class="col-12 col-lg-5">
      <div class="card border-light shadow-sm p-4 mb-4">
        <dl class="row mb-3">
          <dt class="col-5">Pelanggan</dt><dd class="col-7">{{ $receivable->customer->customer_name ?? '-' }}</dd>
          <dt class="col-5">Total</dt><dd class="col-7">{{ rupiah($receivable->amount) }}</dd>
          <dt class="col-5">Dibayar</dt><dd class="col-7">{{ rupiah($receivable->paid_amount) }}</dd>
          <dt class="col-5">Sisa</dt><dd class="col-7"><strong>{{ rupiah($sisa) }}</strong></dd>
          <dt class="col-5">Tanggal</dt><dd class="col-7">{{ tanggal($receivable->receivable_date) }}</dd>
          <dt class="col-5">Jatuh Tempo</dt>
          <dd class="col-7">
            {{ tanggal($receivable->due_date) }}
            @if ($receivable->status != 'paid' && $receivable->due_date && $receivable->due_date->lt(today()))
              <span class="badge-table failed ms-1">Terlambat</span>
            @endif
          </dd>
          <dt class="col-5">Status</dt><dd class="col-7">{!! badge($receivable->status) !!}</dd>
          <dt class="col-5">Transaksi</dt>
          <dd class="col-7">
            @if ($receivable->transaction)
              <a href="{{ route('transaksi.show', $receivable->transaction->id_transaction) }}">{{ $receivable->transaction->invoice_number }}</a>
            @else
              -
            @endif
          </dd>
          <dt class="col-5">Catatan</dt><dd class="col-7">{{ $receivable->note ?: '-' }}</dd>
        </dl>
        <div class="d-flex gap-2">
          <a href="{{ route('piutang.edit', $receivable->id_receivable) }}" class="btn-custom btn-custom-primary btn-custom-sm">Ubah</a>
          <a href="{{ route('piutang.index') }}" class="btn-custom btn-custom-light btn-custom-sm">Kembali</a>
        </div>
      </div>

      @if ($sisa > 0)
        <div class="card border-light shadow-sm p-4">
          <h5 class="card-title mb-3">Terima Cicilan / Pelunasan</h5>

          <form action="{{ route('piutang.pay', $receivable->id_receivable) }}" method="POST">
            @csrf

            <div class="mb-3">
              <label class="form-label-custom">Jumlah Diterima (Rp)</label>
              <input type="number" name="amount" min="1" step="any" class="form-control-custom" value="{{ old('amount', $sisa) }}" required>
              <small class="text-muted">Isi sebesar sisa piutang untuk pelunasan.</small>
              @error('amount')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
              <label class="form-label-custom">Tanggal Terima</label>
              <input type="date" name="payment_date" class="form-control-custom" value="{{ old('payment_date', now()->toDateString()) }}" required>
            </div>

            <div class="mb-3">
              <label class="form-label-custom">Catatan</label>
              <input type="text" name="note" class="form-control-custom">
            </div>

            <button type="submit" class="btn-custom btn-custom-primary">Simpan Pembayaran</button>
          </form>
        </div>
      @endif
    </div>

    <div class="col-12 col-lg-7">
      <div class="table-card-custom">
        <div class="table-header-control"><h5 class="card-title mb-0">Riwayat Pembayaran</h5></div>
        <div class="table-responsive">
          <table class="table-custom">
            <thead>
              <tr><th>Tanggal</th><th>Jumlah</th><th>Catatan</th></tr>
            </thead>
            <tbody>
              @forelse ($receivable->payments as $p)
                <tr>
                  <td>{{ tanggal($p->payment_date) }}</td>
                  <td class="table-amount">{{ rupiah($p->amount) }}</td>
                  <td>{{ $p->note ?: '-' }}</td>
                </tr>
              @empty
                <tr><td colspan="3" class="text-center text-muted-green py-4">Belum ada pembayaran.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
@endsection
