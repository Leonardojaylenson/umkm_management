@extends('template.app')
@section('title', $report->exists ? 'Ubah Laporan' : 'Buat Laporan')
@section('subtitle', 'Pilih periode dan filter. Angka dihitung otomatis saat disimpan.')

@section('content')
  @php
    $filters = $report->filters ?? [];
    $kategoriDipilih = old('id_category', $filters['id_category'] ?? []);
    $metodeDipilih = old('payment_method', $filters['payment_method'] ?? '');
  @endphp

  <div class="card border-light shadow-sm p-4 mx-auto w-100" style="max-width: 720px">
    <form action="{{ $report->exists ? route('laporan.update', $report->id_report) : route('laporan.store') }}" method="POST">
      @csrf
      @if ($report->exists)
        @method('PUT')
      @endif

      <div class="mb-3">
        <label class="form-label-custom">Nama Laporan</label>
        <input type="text" name="report_name" class="form-control-custom" value="{{ old('report_name', $report->report_name) }}" required>
        <small class="text-muted">Contoh: Laporan Oktober 2026</small>
        @error('report_name')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label-custom">Jenis Periode</label>
          <select name="period_type" class="form-select-custom" required>
            <option value="daily" {{ old('period_type', $report->period_type) == 'daily' ? 'selected' : '' }}>Harian</option>
            <option value="weekly" {{ old('period_type', $report->period_type) == 'weekly' ? 'selected' : '' }}>Mingguan</option>
            <option value="monthly" {{ old('period_type', $report->period_type) == 'monthly' ? 'selected' : '' }}>Bulanan</option>
          </select>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label-custom">Tanggal Acuan</label>
          <input type="date" name="reference_date" class="form-control-custom" value="{{ old('reference_date', $report->start_date ? $report->start_date->toDateString() : '') }}" required>
          <small class="text-muted">Mingguan = minggu dari tanggal ini. Bulanan = bulan dari tanggal ini.</small>
          @error('reference_date')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
        </div>
      </div>

      <h6 class="mt-2">Filter (opsional)</h6>

      <div class="mb-3">
        <label class="form-label-custom">Kategori Item</label>
        <div class="d-flex flex-wrap gap-3">
          @forelse ($categories as $category)
            <label class="d-flex align-items-center gap-1">
              <input type="checkbox" name="id_category[]" value="{{ $category->id_category }}" {{ in_array($category->id_category, $kategoriDipilih) ? 'checked' : '' }}>
              {{ $category->category_name }}
            </label>
          @empty
            <span class="text-muted-green">Belum ada kategori.</span>
          @endforelse
        </div>
        <small class="text-muted">Kosongkan untuk semua kategori.</small>
      </div>

      <div class="mb-3">
        <label class="form-label-custom">Metode Bayar</label>
        <select name="payment_method" class="form-select-custom">
          <option value="">- Semua metode -</option>
          <option value="cash" {{ $metodeDipilih == 'cash' ? 'selected' : '' }}>Tunai</option>
          <option value="transfer" {{ $metodeDipilih == 'transfer' ? 'selected' : '' }}>Transfer</option>
          <option value="qris" {{ $metodeDipilih == 'qris' ? 'selected' : '' }}>QRIS</option>
          <option value="e-wallet" {{ $metodeDipilih == 'e-wallet' ? 'selected' : '' }}>E-Wallet</option>
        </select>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn-custom btn-custom-primary">{{ $report->exists ? 'Simpan & Hitung Ulang' : 'Buat Laporan' }}</button>
        <a href="{{ route('laporan.index') }}" class="btn-custom btn-custom-light">Batal</a>
      </div>
    </form>
  </div>
@endsection
