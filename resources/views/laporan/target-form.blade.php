@extends('template.app')
@php $isNew = ! $target->exists; @endphp
@section('title', $isNew ? 'Tetapkan Target Omzet' : 'Ubah Target Omzet')
@section('subtitle', 'Target dihitung dari total omzet pada rentang tanggal.')

@section('content')
  <div class="card border-light shadow-sm p-4" style="max-width: 640px">
    <form action="{{ $isNew ? route('target.store') : route('target.update', $target) }}" method="POST">
      @csrf
      @unless ($isNew) @method('PUT') @endunless

      <x-select name="period_type" label="Jenis Periode" :options="['daily' => 'Harian', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan']" :value="$target->period_type" required />
      <div class="row">
        <div class="col-md-6"><x-input name="start_date" label="Mulai" type="date" :value="$target->start_date?->toDateString()" required /></div>
        <div class="col-md-6"><x-input name="end_date" label="Selesai" type="date" :value="$target->end_date?->toDateString()" required /></div>
      </div>
      <x-input name="target_amount" label="Target Omzet (Rp)" type="number" min="1" step="any" :value="$target->target_amount" required />

      <div class="d-flex gap-2">
        <button type="submit" class="btn-custom btn-custom-primary">Simpan</button>
        <a href="{{ route('target.index') }}" class="btn-custom btn-custom-light">Batal</a>
      </div>
    </form>
  </div>
@endsection
