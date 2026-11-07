@extends('template.app')
@php
  $isNew = ! $report->exists;
  $filters = $report->filters ?? [];
@endphp
@section('title', $isNew ? 'Buat Laporan' : 'Ubah Laporan')
@section('subtitle', 'Pilih periode dan filter. Angka dihitung otomatis saat disimpan.')

@section('content')
  <div class="card border-light shadow-sm p-4" style="max-width: 720px">
    <form action="{{ $isNew ? route('laporan.store') : route('laporan.update', $report) }}" method="POST">
      @csrf
      @unless ($isNew) @method('PUT') @endunless

      <x-input name="report_name" label="Nama Laporan" :value="$report->report_name" hint="Contoh: Laporan Oktober 2026" required />

      <div class="row">
        <div class="col-md-6">
          <x-select name="period_type" label="Jenis Periode" :options="['daily' => 'Harian', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan']" :value="$report->period_type" required />
        </div>
        <div class="col-md-6">
          <x-input name="reference_date" label="Tanggal Acuan" type="date" :value="$report->start_date?->toDateString()" hint="Mingguan = minggu dari tanggal ini, bulanan = bulan dari tanggal ini." required />
        </div>
      </div>

      <h6 class="mt-2">Filter (opsional)</h6>
      <div class="mb-3">
        <label class="form-label-custom">Kategori Item</label>
        <div class="d-flex flex-wrap gap-3">
          @forelse ($categories as $category)
            <label class="d-flex align-items-center gap-1">
              <input type="checkbox" name="id_category[]" value="{{ $category->id_category }}"
                @checked(in_array($category->id_category, old('id_category', $filters['id_category'] ?? [])))>
              {{ $category->category_name }}
            </label>
          @empty
            <span class="text-muted-green">Belum ada kategori.</span>
          @endforelse
        </div>
        <small class="text-muted">Kosongkan untuk semua kategori.</small>
      </div>

      <x-select name="payment_method" label="Metode Bayar" :options="['cash' => 'Tunai', 'transfer' => 'Transfer', 'qris' => 'QRIS', 'e-wallet' => 'E-Wallet']" :value="$filters['payment_method'] ?? null" placeholder="- Semua metode -" />

      <div class="d-flex gap-2">
        <button type="submit" class="btn-custom btn-custom-primary">{{ $isNew ? 'Buat Laporan' : 'Simpan & Hitung Ulang' }}</button>
        <a href="{{ route('laporan.index') }}" class="btn-custom btn-custom-light">Batal</a>
      </div>
    </form>
  </div>
@endsection
