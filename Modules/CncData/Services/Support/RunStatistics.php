<?php

declare(strict_types=1);

namespace Modules\CncData\Services\Support;

/** Small, pure statistics used to check estimates against actual runs. */
final class RunStatistics
{
    /** @param list<float> $values */
    public static function median(array $values): ?float
    {
        $n = count($values);
        if ($n === 0) {
            return null;
        }
        sort($values);
        $mid = intdiv($n, 2);

        return $n % 2 ? (float) $values[$mid] : ($values[$mid - 1] + $values[$mid]) / 2;
    }

    /**
     * Relative spread: median absolute deviation ÷ median. Robust to the odd
     * run where the operator left the table paused at lunch.
     *
     * @param  list<float>  $values
     */
    public static function spread(array $values): ?float
    {
        $med = self::median($values);
        if ($med === null || $med == 0.0) {
            return null;
        }
        $dev = array_map(fn ($v) => abs($v - $med), $values);

        return self::median($dev) / $med;
    }

    /**
     * Least-squares fit of  minutes = a·cutLength + b·pierces  (no intercept).
     * a is minutes per unit of cut length, b is minutes per pierce.
     *
     * @param  list<array{0:float,1:float,2:float}>  $samples  [cutLength, pierces, actualMinutes]
     * @return array{per_length:float,per_pierce:float,rmse:float,n:int}|null
     */
    public static function fitCutAndPierce(array $samples): ?array
    {
        $n = count($samples);
        if ($n < 2) {
            return null;
        }
        $sxx = $sxy = $syy = $sxt = $syt = 0.0;
        foreach ($samples as [$x, $y, $t]) {
            $sxx += $x * $x;
            $sxy += $x * $y;
            $syy += $y * $y;
            $sxt += $x * $t;
            $syt += $y * $t;
        }
        $det = $sxx * $syy - $sxy * $sxy;
        if (abs($det) < 1e-9 * max(1.0, $sxx * $syy)) {
            return null; // cut length and pierces move together; can't separate them yet
        }
        $a = ($sxt * $syy - $syt * $sxy) / $det;
        $b = ($syt * $sxx - $sxt * $sxy) / $det;
        $sse = 0.0;
        foreach ($samples as [$x, $y, $t]) {
            $sse += ($t - ($a * $x + $b * $y)) ** 2;
        }

        return ['per_length' => $a, 'per_pierce' => $b, 'rmse' => sqrt($sse / $n), 'n' => $n];
    }
}
