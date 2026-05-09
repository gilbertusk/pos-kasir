<?php

if (!function_exists('formatRupiah')) {
    function formatRupiah($number, $prefix = 'Rp '): string
    {
        return $prefix . number_format((float) $number, 0, ',', '.');
    }
}
