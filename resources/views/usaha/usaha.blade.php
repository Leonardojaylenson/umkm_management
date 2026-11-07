@extends('template.app')
@section('title', 'Profil Usaha')
@section('subtitle', 'Identitas usaha Anda.')

@section('content')
  <div class="card border-light shadow-sm p-4">
    <div class="d-flex flex-wrap gap-4 align-items-start">
      @if ($business->logo)
        <img src="{{ asset('storage/' . $business->logo) }}" alt="Logo" class="rounded" style="width:110px;height:110px;object-fit:cover">
      @else
        <div class="rounded d-flex align-items-center justify-content-center bg-light" style="width:110px;height:110px">
          <i class="bi bi-shop fs-1 text-muted-green"></i>
        </div>
      @endif

      <div class="flex-grow-1">
        <h3 class="mb-1">{{ $business->business_name }}</h3>
        <p class="text-muted-green mb-3">{{ $business->business_type }}</p>
        <dl class="row mb-0">
          <dt class="col-sm-3">Alamat</dt><dd class="col-sm-9">{{ $business->address ?: '-' }}</dd>
          <dt class="col-sm-3">Telepon</dt><dd class="col-sm-9">{{ $business->phone ?: '-' }}</dd>
          <dt class="col-sm-3">Dibuat</dt><dd class="col-sm-9">{{ tanggal($business->created_at) }}</dd>
        </dl>
      </div>

      <a href="{{ route('usaha.edit') }}" class="btn-custom btn-custom-primary"><i class="bi bi-pencil"></i> Ubah Profil</a>
    </div>
  </div>
@endsection
