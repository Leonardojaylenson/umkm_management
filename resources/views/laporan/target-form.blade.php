@extends('template.app')
@section('title', $target->exists ? 'Ubah Target Omzet' : 'Tetapkan Target Omzet')
@section('subtitle', 'Target dihitung dari total omzet pada rentang tanggal.')

@section('content')
  <div class="card border-light shadow-sm p-4 mx-auto w-100" style="max-width: 640px">
    <form action="{{ $target->exists ? route('target.update', $target->id_revenue_target) : route('target.store') }}" method="POST">
      @csrf
      @if ($target->exists)
        @method('PUT')
      @endif

      <div class="mb-3">
        <label class="form-label-custom">Jenis Periode</label>
        <select name="period_type" class="form-select-custom" required>
          <option value="daily" {{ old('period_type', $target->period_type) == 'daily' ? 'selected' : '' }}>Harian</option>
          <option value="weekly" {{ old('period_type', $target->period_type) == 'weekly' ? 'selected' : '' }}>Mingguan</option>
          <option value="monthly" {{ old('period_type', $target->period_type) == 'monthly' ? 'selected' : '' }}>Bulanan</option>
        </select>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label-custom">Mulai</label>
          <input type="date" name="start_date" class="form-control-custom" value="{{ old('start_date', $target->start_date->toDateString()) }}" required>
          @error('start_date')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label-custom">Selesai</label>
          <input type="date" name="end_date" class="form-control-custom" value="{{ old('end_date', $target->end_date->toDateString()) }}" required>
          @error('end_date')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label-custom">Target Omzet (Rp)</label>
        <input type="number" name="target_amount" min="1" step="any" class="form-control-custom" value="{{ old('target_amount', $target->target_amount) }}" required>
        @error('target_amount')<div class="form-feedback-custom invalid-custom">{{ $message }}</div>@enderror
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn-custom btn-custom-primary">Simpan</button>
        <a href="{{ route('target.index') }}" class="btn-custom btn-custom-light">Batal</a>
      </div>
    </form>
  </div>
@endsection
