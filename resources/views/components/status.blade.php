@props(['value'])

@php
  $map = [
    'paid'         => ['success', 'Lunas'],
    'unpaid'       => ['failed',  'Belum Lunas'],
    'down_payment' => ['pending', 'DP'],
    'partial'      => ['pending', 'Dicicil'],
    'completed'    => ['success', 'Selesai'],
    'cancelled'    => ['failed',  'Dibatalkan'],
    'active'       => ['success', 'Aktif'],
    'inactive'     => ['failed',  'Nonaktif'],
  ];
  [$class, $label] = $map[$value] ?? ['pending', $value];
@endphp

<span class="badge-table {{ $class }}">{{ $label }}</span>
