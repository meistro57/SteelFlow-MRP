<?php

declare(strict_types=1);

namespace Modules\CncData\Engine;

/**
 * Number formatting that matches the JS engine, so warning messages and
 * rounded figures come out byte-for-byte the same in both implementations.
 */
final class Js
{
    /** Math.round(): halves round toward +infinity (PHP round() goes away from zero). */
    public static function round(float $v): float
    {
        return floor($v + 0.5);
    }

    /** Math.round(v * 1e4) / 1e4 */
    public static function r4(float $v): float
    {
        $r = floor($v * 1e4 + 0.5) / 1e4;

        return $r == 0.0 ? 0.0 : $r; // no -0
    }

    /** CNC Bench fmt(): clamp tiny values to 0, then toFixed(d). */
    public static function fixed(float $n, int $d = 3): string
    {
        if (abs($n) < 5 * 10 ** -($d + 1)) {
            $n = 0.0;
        }

        return number_format($n, $d, '.', '');
    }

    /** String form of a JS number in a template literal (155, 0.13). */
    public static function num(float|int $v): string
    {
        if (is_float($v) && floor($v) == $v && abs($v) < 1e15) {
            return (string) (int) $v;
        }

        return (string) $v;
    }
}
