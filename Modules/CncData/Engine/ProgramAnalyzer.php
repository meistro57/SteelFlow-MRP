<?php

declare(strict_types=1);

namespace Modules\CncData\Engine;

/**
 * Turns a Phoenix plasma program into the shared analysis contract
 * `steelflow.cnc-analysis/1` — the same JSON the CNC Bench web app produces.
 *
 *   $analysis = (new ProgramAnalyzer)->analyze($text, ['rapid_rate' => 600]);
 *
 * Lengths are in programme units (in or mm), times in minutes, lines 1-based.
 */
final class ProgramAnalyzer
{
    public const SCHEMA = 'steelflow.cnc-analysis/1';

    public const SEVERITY = [
        'TORCH_ALREADY_ON' => 'error',
        'TORCH_ALREADY_OFF' => 'warning',
        'END_TORCH_ON' => 'error',
        'END_KERF_ON' => 'warning',
        'NO_MOTION_MODE' => 'error',
        'RAPID_TORCH_ON' => 'error',
        'ARC_RADIUS' => 'warning',
        'NO_FEED' => 'error',
        'NO_UNITS' => 'info',
        'NO_END' => 'warning',
        'OFF_PLATE' => 'error',
        'CHART_FEED' => 'warning',
        'CHART_KERF' => 'warning',
    ];

    public function __construct(private readonly PhoenixProgramParser $parser = new PhoenixProgramParser) {}

    /**
     * @param  array{rapid_rate?:float|int}  $options
     * @return array<string,mixed>
     */
    public function analyze(string $text, array $options = []): array
    {
        $rapidRate = (float) ($options['rapid_rate'] ?? 600);
        if ($rapidRate <= 0) {
            $rapidRate = 600.0;
        }
        $p = $this->parser->parse($text, $rapidRate);

        $pm = preg_match('/(\d+)\s*Amp\s+([A-Za-z0-9]+)\/([A-Za-z0-9]+)/i', $p->process, $m) ? $m : null;
        $cutPierces = array_values(array_filter($p->pierces, fn ($x) => $x['cut']));

        $warnings = array_map(fn ($w) => [
            'line' => $w['line'] + 1,
            'code' => $w['code'],
            'severity' => self::SEVERITY[$w['code']] ?? 'warning',
            'message' => $w['message'],
        ], $p->warnings);

        $chart = XprCutChart::forProgram($p);
        $row = $chart['row'] ?? null;
        $chartOut = null;
        $pierceMin = 0.0;
        if ($row !== null) {
            $cmp = XprCutChart::compare($p, $row);
            foreach ($cmp['feeds'] as $f) {
                if (abs($f['dev']) > XprCutChart::FEED_TOLERANCE) {
                    $s = $this->firstPlainCut($p, 'feed', $f['f']);
                    $warnings[] = ['line' => $s['line'] + 1, 'code' => 'CHART_FEED', 'severity' => 'warning',
                        'message' => 'Cut feed F'.Js::fixed($f['f'], 0).' is '.Js::num(Js::round($f['dev'] * 100)).'% off the cut chart ('.Js::num($row['speed']).' in/min)'];
                }
            }
            foreach ($cmp['kerfs'] as $k) {
                if (abs($k['dev']) > XprCutChart::KERF_TOLERANCE) {
                    $s = $this->firstPlainCut($p, 'kerf', $k['k']);
                    $warnings[] = ['line' => $s['line'] + 1, 'code' => 'CHART_KERF', 'severity' => 'warning',
                        'message' => 'Kerf '.Js::fixed($k['k'], 3).' differs from the cut chart ('.Js::num($row['kerf']).' in)'];
                }
            }
            usort($warnings, fn ($a, $b) => $a['line'] <=> $b['line']);

            $pierceMin = $row['delay'] * count($cutPierces) / 60;
            $chartOut = [
                'amps' => $chart['amps'],
                'name' => $chart['chart']['name'],
                'source' => XprCutChart::SOURCE.' p.'.$chart['chart']['page'],
                'thickness' => $row['t'],
                'speed' => $row['speed'],
                'arc_volts' => $row['volts'],
                'pierce_delay_s' => $row['delay'],
                'kerf' => $row['kerf'],
                'note' => $row['note'] !== '' ? $row['note'] : null,
                'feed_ok' => array_reduce($cmp['feeds'], fn ($ok, $f) => $ok && abs($f['dev']) <= XprCutChart::FEED_TOLERANCE, true),
                'kerf_ok' => array_reduce($cmp['kerfs'], fn ($ok, $k) => $ok && abs($k['dev']) <= XprCutChart::KERF_TOLERANCE, true),
            ];
        }

        $feeds = [];
        $kerfs = [];
        foreach ($p->segments as $s) {
            if ($s['type'] === 'cut') {
                $fk = (string) $s['feed'];
                $feeds[$fk] ??= ['feed' => $s['feed'], 'length' => 0.0];
                $feeds[$fk]['length'] += $s['len'];
                $kerfs[(string) $s['kerf']] = $s['kerf'];
            }
        }
        $feeds = array_values($feeds);
        usort($feeds, fn ($a, $b) => $a['feed'] <=> $b['feed']);
        $kerfs = array_values($kerfs);
        sort($kerfs);

        $t = [
            'cut' => $p->minutes['cut'], 'mark' => $p->minutes['mark'], 'travel' => $p->minutes['travel'],
            'rapid' => $p->minutes['rapid'], 'dwell' => $p->dwellSeconds / 60, 'pierce_delay' => $pierceMin,
        ];

        return [
            'schema' => self::SCHEMA,
            'units' => $p->units,
            'line_count' => count($p->lines),
            'material' => [
                'name' => $p->material !== '' ? $p->material : null,
                'thickness' => $p->thickness ?: null,
                'sheet' => $p->sheet ? ['x' => $p->sheet['w'], 'y' => $p->sheet['h']] : null,
            ],
            'process' => [
                'label' => $p->process !== '' ? $p->process : null,
                'amps' => $pm ? (int) $pm[1] : null,
                'plasma_gas' => $pm[2] ?? null,
                'shield_gas' => $pm[3] ?? null,
            ],
            'lengths' => array_map([Js::class, 'r4'], [
                'cut' => $p->lengths['cut'], 'mark' => $p->lengths['mark'],
                'travel' => $p->lengths['travel'], 'rapid' => $p->lengths['rapid'],
            ]),
            'pierces' => [
                'cut' => count($cutPierces),
                'true_hole' => count(array_filter($cutPierces, fn ($x) => $x['th'])),
                'mark_starts' => count($p->pierces) - count($cutPierces),
            ],
            'times_min' => array_map([Js::class, 'r4'], $t + ['total' => $t['cut'] + $t['mark'] + $t['travel'] + $t['rapid'] + $t['dwell'] + $t['pierce_delay']]),
            'cut_feeds' => array_map(fn ($f) => ['feed' => $f['feed'], 'length' => Js::r4($f['length'])], $feeds),
            'kerf_values' => $kerfs,
            'extents' => ['path' => self::box($p->bounds), 'cut' => self::box($p->cutBounds)],
            'chart' => $chartOut,
            'warnings' => $warnings,
            'options' => ['rapid_rate' => $rapidRate],
        ];
    }

    private function firstPlainCut(ParsedProgram $p, string $key, float $value): array
    {
        foreach ($p->segments as $s) {
            if ($s['type'] === 'cut' && ! $s['th'] && $s[$key] === $value) {
                return $s;
            }
        }

        return ['line' => 0];
    }

    private static function box(array $b): ?array
    {
        if (! is_finite($b['minx'])) {
            return null;
        }

        return ['min_x' => Js::r4($b['minx']), 'min_y' => Js::r4($b['miny']), 'max_x' => Js::r4($b['maxx']), 'max_y' => Js::r4($b['maxy'])];
    }
}
