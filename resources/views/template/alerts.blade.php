@php
  $flash = [
    'success' => ['alert-custom-success', 'bi-check-circle-fill'],
    'error'   => ['alert-custom-danger', 'bi-exclamation-triangle-fill'],
    'warning' => ['alert-custom-warning', 'bi-exclamation-circle-fill'],
  ];
@endphp

@foreach ($flash as $key => [$class, $icon])
  @if (session($key))
    <div class="alert-custom {{ $class }}">
      <i class="bi {{ $icon }} alert-custom-icon"></i>
      <div class="alert-custom-content">{{ session($key) }}</div>
      <button class="alert-custom-close" type="button" aria-label="Tutup" onclick="this.parentElement.remove();">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>
  @endif
@endforeach

@foreach (['items', 'status'] as $key)
  @error($key)
    <div class="alert-custom alert-custom-danger">
      <i class="bi bi-exclamation-triangle-fill alert-custom-icon"></i>
      <div class="alert-custom-content">{{ $message }}</div>
    </div>
  @enderror
@endforeach
