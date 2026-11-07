<?php

use Carbon\Carbon;

if (! function_exists('rupiah')) {
    function rupiah($amount): string
    {
        return 'Rp ' . number_format((float) $amount, 0, ',', '.');
    }
}

if (! function_exists('tanggal')) {
    function tanggal($date, bool $withTime = false): string
    {
        if (! $date) {
            return '-';
        }

        return Carbon::parse($date)->translatedFormat($withTime ? 'd M Y H:i' : 'd M Y');
    }
}

if (! function_exists('qty')) {
    function qty($value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',');
    }
}
