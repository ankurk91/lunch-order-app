<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;

/**
 * Compare given route with current route and return output if they match.
 *
 * @param $route String
 * @param string $output
 * @return null|string
 */
function isActiveRoute($route, $output = "active")
{
    return (Route::currentRouteName() === $route) ? $output : null;
}

/**
 * Convert a number into readable currency format
 *
 * @param $number
 * @param string $sign
 * @return string
 */
function money($number, $sign = '₹')
{
    return $sign . number_format((float)$number, 2, '.', ',');
}

/**
 * Get months names with numbers
 *
 * @param int $from
 * @param int $to
 * @return array
 */
function monthsWithNames($from = 1, $to = 12)
{
    $months = [];

    for ($m = $from; $m <= $to; ++$m) {
        $months[$m] = date('F', mktime(0, 0, 0, $m, 1));
    }

    return $months;
}

