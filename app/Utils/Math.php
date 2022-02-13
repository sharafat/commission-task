<?php

namespace App\Utils;

use RuntimeException;

class Math
{
    /**
     * Credit: https://gist.github.com/gh640/6d65226c6203f2cb0ebe42fbddca8ece
     */
    public static function roundUp(float $value, ?int $precision = null): float
    {
        if (null === $precision) {
            return ceil($value);
        }

        if ($precision < 0) {
            throw new RuntimeException('Invalid precision');
        }

        $reg = $value + 0.5 / (10 ** $precision);

        return round($reg, $precision, $reg > 0 ? PHP_ROUND_HALF_DOWN : PHP_ROUND_HALF_UP);
    }

    /**
     * Credit: https://gist.github.com/gh640/6d65226c6203f2cb0ebe42fbddca8ece
     */
    public static function roundDown(float $value, ?int $precision = null): float
    {
        if (null === $precision) {
            return floor($value);
        }
        if ($precision < 0) {
            throw new RuntimeException('Invalid precision');
        }

        $reg = $value - 0.5 / (10 ** $precision);

        return round($reg, $precision, $reg > 0 ? PHP_ROUND_HALF_UP : PHP_ROUND_HALF_DOWN);
    }

    /**
     * Credit: https://stackoverflow.com/questions/2430084/php-get-number-of-decimal-digits#answer-60638478
     */
    public static function decimalDigits(float|string $number): int
    {
        return (int) strpos(strrev((string) $number), '.');
    }
}
