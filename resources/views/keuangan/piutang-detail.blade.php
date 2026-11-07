@extends('template.app')
@section('title', 'Detail Piutang')
@section('subtitle', $receivable->customer->customer_name ?? '')

@section('content')
  <div class="row g-4">
    <div class="col-12 col-lg-5">
      <div class="card border-light shadow-sm p-4 mb-4">
        <dl class="row mb-3">
          <dt class="col-5">Pelanggan</dt><dd class="col-7">{{ $receivable->customer->customer_name ?? '-' }}</dd>
          <dt class="col-5">Total</dt><dd class="col-7">{{ rupiah($receivable->amount) }}</dd>
          <dt class="col-5">Dibayar</dt><dd class="col-7">{{ rupiah($receivable->paid_amount) }}</dd>
          <dt class="col-5">Sisa</dt><dd class="col-7"><strong>{{ rupiah($receivable->remaining) }}</strong></dd>
          <dt class="col-5">Tanggal</dt><dd class="col-7">{{ tanggal($receivable->receivable_date) }}</dd>
          <dt class="col-5">Jatuh Tempo</dt>
          <dd class="col-7">{{ tanggal($receivable->due_date) }} @if ($receivable->is_overdue)<span class="badge-table failed ms-1">Terlambat</span>@endif</dd>
          <dt class="col-5">Status</dt><dd class="col-7"><x-status :value="$receivable->status" /></dd>
          <dt class="col-5">Transaksi</dt>
          <dd class="col-7">
            @if ($receivable->transaction)
              <a href="{{ route('transaksi.show', $receivable->transaction) }}">{{ $receivable->transaction->invoice_number }}</a>
            @else - @endif
          </dd>
          <dt class="col-5">Catatan</dt><dd class="col-7">{{ $receivable->note ?: '-' }}</dd>
        </dl>
        <div class="d-flex gap-2">
          <a href="{{ route('piutang.edit', $receivable) }}" class="btn-custom btn-custom-primary btn-custom-sm">Ubah</a>
          <a href="{{ route('piutang.index') }}" class="btn-custom btn-custom-light btn-custom-sm">Kembali</a>
        </div>
      </div>

      @if ($receivable->remaining > 0)
        <div class="card border-light shadow-sm p-4">
          <h5 class="card-title mb-3">Terima Cicilan / Pelunasan</h5>
          <form action="{{ route('piutang.pay', $receivable) }}" method="POST">
            @csrf
            <x-input name="amount" label="Jumlah Diterima (Rp)" type="number" min="1" step="any" :value="$receivable->remaining" hint="Isi sisa piutang untuk pelunasan." required />
            <x-input name="payment_date" label="Tanggal Terima" type="date" :value="now()->toDateString()" required />
            <x-input name="note" label="Catatan" />
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
            <thead><tr><th>Tanggal</th><th>Jumlah</th><th>Catatan</th></tr></thead>
            <tbody>
              @forelse ($receivable->payments as $p)
                <tr><td>{{ tanggal($p->payment_date) }}</td><td class="table-amount">{{ rupiah($p->amount) }}</td><td>{{ $p->note ?: '-' }}</td></tr>
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
