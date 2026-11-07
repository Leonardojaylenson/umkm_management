@props(['label', 'value', 'sub' => null, 'icon' => 'bi-graph-up'])

<div class="card card-stat h-100">
  <div class="card-header">
    <span class="stat-label">{{ $label }}</span>
    <i class="bi {{ $icon }} text-muted-green fs-5"></i>
  </div>
  <div class="stat-value">{{ $value }}</div>
  @if ($sub)<div class="text-muted-green small mt-1">{{ $sub }}</div>@endif
</div>
