<?php

use Carbon\Carbon;

function rupiah($amount)
{
    return 'Rp ' . number_format((float) $amount, 0, ',', '.');
}

function tanggal($date, $withTime = false)
{
    if (! $date) {
        return '-';
    }

    return Carbon::parse($date)->translatedFormat($withTime ? 'd M Y H:i' : 'd M Y');
}

function qty($value)
{
    return rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',');
}

function badge($status)
{
    $list = [
        'paid'         => ['success', 'Lunas'],
        'unpaid'       => ['failed', 'Belum Lunas'],
        'down_payment' => ['pending', 'DP'],
        'partial'      => ['pending', 'Dicicil'],
        'completed'    => ['success', 'Selesai'],
        'cancelled'    => ['failed', 'Dibatalkan'],
        'active'       => ['success', 'Aktif'],
        'inactive'     => ['failed', 'Nonaktif'],
    ];

    $warna = $list[$status][0];
    $teks  = $list[$status][1];

    return '<span class="badge-table ' . $warna . '">' . $teks . '</span>';
}
