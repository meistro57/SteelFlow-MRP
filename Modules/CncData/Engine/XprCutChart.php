<?php

declare(strict_types=1);

namespace Modules\CncData\Engine;

/**
 * Hypertherm XPR mild steel O2 cut chart data, English units.
 *
 * Source: XPR Cut Charts instruction manual 809830 R1. Typed in by hand from
 * the PDF, where some columns span several rows, so treat a value as a
 * reference and confirm against the chart before changing a program for it.
 *
 * Row: [thickness in, cut speed in/min, arc volts, pierce delay s, kerf in, note]
 */
final class XprCutChart
{
    public const SOURCE = 'XPR Cut Charts IM 809830 R1';

    /** Matching window for a programme's plate thickness against a chart row. */
    private const THICKNESS_TOLERANCE = 0.02;

    public const FEED_TOLERANCE = 0.10;   // flag a feed more than 10% off the chart

    public const KERF_TOLERANCE = 0.015;  // flag a kerf more than 0.015 in off the chart

    public const CHARTS = [
        130 => [
            'name' => 'Mild steel 130 A, O2 plasma / Air shield',
            'page' => '24–25',
            'parts' => ['Shield' => '420255', 'Nozzle' => '420252', 'Swirl ring' => '420242', 'Electrode' => '420249'],
            'rows' => [
                [0.135, 240, 134, 0.1, 0.09], [0.1875, 190, 134, 0.2, 0.09], [0.25, 150, 135, 0.2, 0.09], [0.3125, 130, 134, 0.3, 0.09],
                [0.375, 110, 136, 0.3, 0.10], [0.5, 80, 138, 0.5, 0.10], [0.625, 60, 144, 0.7, 0.11], [0.75, 45, 147, 1.0, 0.12],
                [1, 20, 164, 1.8, 0.16], [1.25, 16, 165, 0.3, 0.18, 'edge start'], [1.5, 10, 174, 0.3, 0.23, 'edge start'],
            ],
        ],
        170 => [
            'name' => 'Mild steel 170 A, O2 plasma / Air shield',
            'page' => '26–27',
            'parts' => ['Shield' => '420513', 'Nozzle' => '420261', 'Swirl ring' => '420260', 'Electrode' => '420258'],
            'rows' => [
                [0.25, 200, 127, 0.3, 0.11], [0.3125, 170, 128, 0.3, 0.11], [0.375, 140, 128, 0.3, 0.11], [0.5, 115, 129, 0.5, 0.10],
                [0.625, 80, 135, 0.6, 0.11], [0.75, 65, 137, 0.8, 0.13], [1, 45, 142, 1.0, 0.14], [1.25, 30, 145, 3.0, 0.18],
                [1.5, 20, 151, 0.3, 0.19, 'edge start'], [1.75, 14, 157, 0.3, 0.22, 'edge start'], [2, 10, 163, 0.5, 0.24, 'edge start'],
            ],
        ],
        300 => [
            'name' => 'Mild steel 300 A, O2 plasma / Air shield',
            'page' => '28–29',
            'parts' => ['Shield' => '420491', 'Nozzle' => '420279', 'Swirl ring' => '420406', 'Electrode' => '420276'],
            'rows' => [
                [0.5, 155, 147, 0.4, 0.19, 'N2 shield on this row'], [0.625, 130, 151, 0.5, 0.15], [0.75, 105, 154, 0.7, 0.16],
                [0.875, 90, 154, 0.7, 0.19], [1, 75, 156, 1.0, 0.18], [1.25, 55, 163, 1.8, 0.20], [1.5, 40, 165, 3.0, 0.22],
                [1.75, 30, 170, 4.5, 0.22], [2, 21, 175, 8.0, 0.24, 'N2 shield; pierce needs VWI/OptiMix'],
                [2.25, 17, 181, 1.5, 0.26, 'N2 shield, edge start'], [2.5, 14, 185, 1.5, 0.27, 'N2 shield, edge start'],
                [2.75, 10, 192, 1.5, 0.31, 'N2 shield, edge start'], [3, 7, 195, 1.5, 0.38, 'N2 shield, edge start'],
            ],
        ],
    ];

    public const COMMON_PARTS = ['Shield retaining cap' => '420200', 'Nozzle retaining cap' => '420365', 'Water tube' => '420368'];

    /**
     * Chart row for this programme, or null when the material/process isn't covered.
     *
     * @return array{amps:int,chart:array,row:?array{t:float,speed:float,volts:float,delay:float,kerf:float,note:string}}|null
     */
    public static function forProgram(ParsedProgram $p): ?array
    {
        if ($p->units !== 'in' || ! preg_match('/mild/i', $p->material) || ! $p->thickness) {
            return null;
        }
        if (! preg_match('/(\d+)\s*Amp/i', $p->process, $m) || ! isset(self::CHARTS[(int) $m[1]])) {
            return null;
        }
        $amps = (int) $m[1];
        $chart = self::CHARTS[$amps];
        foreach ($chart['rows'] as $r) {
            if (abs($r[0] - $p->thickness) < self::THICKNESS_TOLERANCE) {
                return ['amps' => $amps, 'chart' => $chart, 'row' => [
                    't' => (float) $r[0], 'speed' => (float) $r[1], 'volts' => (float) $r[2],
                    'delay' => (float) $r[3], 'kerf' => (float) $r[4], 'note' => $r[5] ?? '',
                ]];
            }
        }

        return ['amps' => $amps, 'chart' => $chart, 'row' => null];
    }

    /**
     * Compare the programme's plain cuts (not True Hole) with the chart row.
     *
     * @return array{feeds:list<array{f:float,len:float,dev:float}>,kerfs:list<array{k:float,len:float,dev:float}>}
     */
    public static function compare(ParsedProgram $p, array $row): array
    {
        $byF = [];
        $byK = [];
        foreach ($p->segments as $s) {
            if ($s['type'] === 'cut' && ! $s['th']) {
                $fk = (string) $s['feed'];
                $byF[$fk] ??= ['v' => $s['feed'], 'len' => 0.0];
                $byF[$fk]['len'] += $s['len'];
                $kk = (string) $s['kerf'];
                $byK[$kk] ??= ['v' => $s['kerf'], 'len' => 0.0];
                $byK[$kk]['len'] += $s['len'];
            }
        }
        $feeds = array_map(fn ($e) => ['f' => $e['v'], 'len' => $e['len'], 'dev' => ($e['v'] - $row['speed']) / $row['speed']], array_values($byF));
        $kerfs = array_map(fn ($e) => ['k' => $e['v'], 'len' => $e['len'], 'dev' => $e['v'] - $row['kerf']], array_values($byK));
        usort($feeds, fn ($a, $b) => $b['len'] <=> $a['len']);
        usort($kerfs, fn ($a, $b) => $b['len'] <=> $a['len']);

        return ['feeds' => $feeds, 'kerfs' => $kerfs];
    }
}
