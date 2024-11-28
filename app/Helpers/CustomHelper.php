<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class CustomHelper
{
    public static function generateHexId($prefix = '', $case = 'upper') {
        $timestamp = dechex((int) (microtime(true) * 1000)); // Millisecond precision
        $random = Str::random(4);
        $id = $prefix . $timestamp . $random;

        return ($case == 'lower') ? strtolower($id) : strtoupper($id);
    }
}
