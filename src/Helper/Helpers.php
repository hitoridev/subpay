<?php

if (!function_exists('CurrencyIDR')) {
    function CurrencyIDR($value)
    {
        $format = new NumberFormatter('id_ID', NumberFormatter::DECIMAL);
        $format->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);
        return $format->format($value);
    }
}

if (!function_exists('IDRToNumeric')) {
    function IDRToNumeric($value)
    {
        return preg_replace('/[^0-9,]/', '', $value);
    }
}
