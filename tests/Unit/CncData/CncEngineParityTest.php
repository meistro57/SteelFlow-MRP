<?php

/*
| The PHP engine must produce the same steelflow.cnc-analysis/1 JSON as the
| CNC Bench web app for the same file. The *.expected.json fixtures were made
| by the JS engine; regenerate them from CNC Bench when the contract changes.
*/

use Modules\CncData\Engine\Js;
use Modules\CncData\Engine\ProgramAnalyzer;

function cncFixtures(): array
{
    $out = [];
    foreach (glob(__DIR__.'/../../Fixtures/cnc/*.CNC') as $f) {
        $out[basename($f)] = [$f];
    }

    return $out;
}

function cncDiff(mixed $a, mixed $b, string $path = ''): array
{
    if (is_array($a) && is_array($b)) {
        $diffs = [];
        foreach (array_unique(array_merge(array_keys($a), array_keys($b))) as $k) {
            if (! array_key_exists($k, $a) || ! array_key_exists($k, $b)) {
                $diffs[] = "$path.$k missing";

                continue;
            }
            $diffs = array_merge($diffs, cncDiff($a[$k], $b[$k], "$path.$k"));
        }

        return $diffs;
    }
    if (is_numeric($a) && is_numeric($b) && ! is_string($a) && ! is_string($b)) {
        return abs($a - $b) < 1e-6 ? [] : ["$path: ".json_encode($a).' vs '.json_encode($b)];
    }

    return $a === $b ? [] : ["$path: ".json_encode($a).' vs '.json_encode($b)];
}

it('matches the CNC Bench engine', function (string $file) {
    $expected = json_decode(file_get_contents(preg_replace('/\.CNC$/', '.expected.json', $file)), true);
    $actual = (new ProgramAnalyzer)->analyze(file_get_contents($file));

    expect(cncDiff($actual, $expected))->toBe([]);
})->with(cncFixtures());

it('reads the canopy nest against the 170 A chart', function () {
    $a = (new ProgramAnalyzer)->analyze(file_get_contents(__DIR__.'/../../Fixtures/cnc/3365_old_mill_rd-canopy01.CNC'));

    expect($a['material'])->toMatchArray(['name' => 'Mild Steel', 'thickness' => 0.75])
        ->and($a['process']['amps'])->toBe(170)
        ->and($a['pierces']['cut'])->toBe(19)
        ->and($a['pierces']['true_hole'])->toBe(8)
        ->and($a['chart']['speed'])->toEqual(65)
        ->and($a['chart']['feed_ok'])->toBeTrue()
        ->and($a['warnings'])->toBe([]);
});

it('flags a broken program with error codes and lines', function () {
    $a = (new ProgramAnalyzer)->analyze(file_get_contents(__DIR__.'/../../Fixtures/cnc/broken.CNC'));
    $codes = array_column($a['warnings'], 'code');

    expect($codes)->toContain('TORCH_ALREADY_ON', 'RAPID_TORCH_ON', 'ARC_RADIUS', 'OFF_PLATE', 'NO_END')
        ->and(collect($a['warnings'])->firstWhere('code', 'RAPID_TORCH_ON')['line'])->toBe(11);
});

it('catches feed and kerf that drift from the cut chart', function () {
    $a = (new ProgramAnalyzer)->analyze(file_get_contents(__DIR__.'/../../Fixtures/cnc/chart-deviation.CNC'));

    expect($a['chart']['feed_ok'])->toBeFalse()
        ->and($a['chart']['kerf_ok'])->toBeFalse()
        ->and(array_column($a['warnings'], 'code'))->toBe(['CHART_FEED', 'CHART_KERF']);
});

it('scales rapids for metric programs and handles an empty file', function () {
    $mm = (new ProgramAnalyzer)->analyze("G21\nG91\nG00X600.\nM02\n", ['rapid_rate' => 600]);
    expect($mm['times_min']['rapid'])->toEqual(Js::r4(600 / (600 * 25.4)));

    $empty = (new ProgramAnalyzer)->analyze('');
    expect($empty['lengths']['cut'])->toEqual(0)
        ->and($empty['extents']['path'])->toBeNull();
});
