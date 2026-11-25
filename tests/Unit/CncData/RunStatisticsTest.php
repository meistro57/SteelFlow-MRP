<?php

use Modules\CncData\Services\Support\RunStatistics;

it('takes the median of odd and even lists', function () {
    expect(RunStatistics::median([3.0, 1.0, 2.0]))->toEqual(2.0)
        ->and(RunStatistics::median([4.0, 1.0, 2.0, 3.0]))->toEqual(2.5)
        ->and(RunStatistics::median([]))->toBeNull();
});

it('measures spread without being thrown by one long pause', function () {
    $ratios = [1.10, 1.12, 1.09, 1.11, 2.40];
    expect(RunStatistics::spread($ratios))->toBeLessThan(0.02);
});

it('recovers minutes per inch and per pierce from runs', function () {
    // Truth: 0.016 min per inch of cut, 0.05 min (3 s) per pierce
    $samples = [];
    foreach ([[400, 20], [250, 60], [900, 12], [120, 80], [600, 35]] as [$len, $p]) {
        $samples[] = [(float) $len, (float) $p, 0.016 * $len + 0.05 * $p];
    }
    $fit = RunStatistics::fitCutAndPierce($samples);

    expect($fit['per_length'])->toEqualWithDelta(0.016, 1e-9)
        ->and($fit['per_pierce'])->toEqualWithDelta(0.05, 1e-9)
        ->and($fit['rmse'])->toBeLessThan(1e-9);
});

it('refuses a fit when cut and pierces move together', function () {
    expect(RunStatistics::fitCutAndPierce([[100.0, 10.0, 5.0], [200.0, 20.0, 10.0]]))->toBeNull()
        ->and(RunStatistics::fitCutAndPierce([[100.0, 10.0, 5.0]]))->toBeNull();
});
