<?php

declare(strict_types=1);

namespace Modules\CncData\Engine;

/**
 * Walks a Hypertherm Phoenix EIA-274 plasma program (as posted by ProNest)
 * and produces toolpath segments, pierces, lengths, motion times and warnings.
 *
 * This is a line-for-line port of the CNC Bench parser (parseNC in the web
 * app). The two must stay in step: tests/Fixtures/cnc holds golden JSON made
 * by the JS engine, and CncEngineParityTest fails if this port drifts.
 */
final class PhoenixProgramParser
{
    private const WORD_RX = '/([A-Z])\s*([-+]?(?:\d+\.?\d*|\.\d+))/';

    private const MAX_WARNINGS = 300;

    private const MAX_OFF_PLATE_LINES = 25;

    /** @param float $rapidRate rapid traverse in in/min (scaled ×25.4 for G21 programs) */
    public function parse(string $text, float $rapidRate = 600.0): ParsedProgram
    {
        $lines = preg_split('/\r?\n/', $text) ?: [''];

        $x = 0.0;
        $y = 0.0;
        $inc = false;
        $units = 'in';
        $unitsSet = false;
        $motion = -1;
        $torch = false;
        $comp = 40;
        $kerf = 0.0;
        $feed = 0.0;
        $ended = false;
        $th = false;
        $ar = false;

        $segments = [];
        $pierces = [];
        $warnings = [];
        $sheet = null;
        $material = '';
        $thick = 0.0;
        $process = '';
        $dwell = 0.0;

        $warn = function (int $line, string $code, string $msg) use (&$warnings): void {
            if (count($warnings) < self::MAX_WARNINGS) {
                $warnings[] = ['line' => $line, 'code' => $code, 'message' => $msg];
            }
        };

        foreach ($lines as $i => $raw) {
            $comments = [];
            $code = preg_replace_callback('/\(([^)]*)\)?/', function ($m) use (&$comments) {
                $comments[] = $m[1] ?? '';

                return ' ';
            }, $raw) ?? $raw;
            $sc = strpos($code, ';');
            if ($sc !== false) {
                $comments[] = substr($code, $sc + 1);
                $code = substr($code, 0, $sc);
            }
            $up = strtoupper($code);

            foreach ($comments as $c) {
                if (preg_match('/Load Material:\s*([^;]+);\s*([\d.]+)"?\s*x\s*([\d.]+)"?\s*;\s*([\d.]+)/i', $c, $m)) {
                    $material = trim($m[1]);
                    $sheet = ['w' => (float) $m[2], 'h' => (float) $m[3]];
                    $thick = (float) $m[4];
                } elseif (preg_match('/\d+\s*amp|O2|N2|Air\//i', $c) && $process === '') {
                    $process = trim($c);
                }
            }

            /** @var list<array{l:string,v:float,s:string}> $words */
            $words = [];
            if (preg_match_all(self::WORD_RX, $up, $mm, PREG_SET_ORDER)) {
                foreach ($mm as $m) {
                    $words[] = ['l' => $m[1], 'v' => (float) $m[2], 's' => $m[2]];
                }
            }
            $trimmed = trim($up);
            $flags = $trimmed === '' ? [] : array_values(array_filter(
                preg_split('/\s+/', $trimmed) ?: [],
                fn ($t) => (bool) preg_match('/^[A-Z]{1,3}$/', $t),
            ));
            $get = function (string $l) use ($words): ?float {
                foreach ($words as $w) {
                    if ($w['l'] === $l) {
                        return $w['v'];
                    }
                }

                return null;
            };

            $nonMotion = false;
            foreach ($words as $w) {
                if ($w['l'] !== 'G') {
                    continue;
                }
                $c = $w['v'];
                if ($c == 20 || $c == 70) {
                    $units = 'in';
                    $unitsSet = true;
                } elseif ($c == 21 || $c == 71) {
                    $units = 'mm';
                    $unitsSet = true;
                } elseif ($c == 90) {
                    $inc = false;
                } elseif ($c == 91) {
                    $inc = true;
                } elseif ($c == 40 || $c == 41 || $c == 42) {
                    $comp = (int) $c;
                } elseif ($c == 0 || $c == 1 || $c == 2 || $c == 3) {
                    $motion = (int) $c;
                } elseif ($c == 4) {
                    $nonMotion = true;
                    $dwell += $get('X') ?? $get('P') ?? 0.0;
                } elseif ($c == 43) {
                    $nonMotion = true;
                    $k = $get('X');
                    if ($k !== null) {
                        $kerf = $k;
                    }
                } else {
                    $nonMotion = true; // G59, G92, etc.: their words are parameters, not motion
                }
            }
            if (! $nonMotion) {
                $f = $get('F');
                if ($f !== null) {
                    $feed = $f;
                }
            }

            foreach ($words as $w) {
                if ($w['l'] !== 'M') {
                    continue;
                }
                $c = $w['v'];
                if ($c == 7) {
                    if ($torch) {
                        $warn($i, 'TORCH_ALREADY_ON', 'M07 cut-on while the torch is already on');
                    }
                    $torch = true;
                    $th = in_array('TH', $flags, true);
                    $ar = in_array('AR', $flags, true);
                    $pierces[] = ['x' => $x, 'y' => $y, 'line' => $i, 'cut' => $comp !== 40, 'th' => $th];
                } elseif ($c == 8) {
                    if (! $torch) {
                        $warn($i, 'TORCH_ALREADY_OFF', 'M08 cut-off while the torch is already off');
                    }
                    $torch = false;
                } elseif ($c == 2 || $c == 30) {
                    $ended = true;
                    if ($torch) {
                        $warn($i, 'END_TORCH_ON', 'Program ends with the torch still on');
                    }
                    if ($comp !== 40) {
                        $warn($i, 'END_KERF_ON', 'Program ends with kerf compensation still on (no G40)');
                    }
                }
            }

            $X = $get('X');
            $Y = $get('Y');
            $I = $get('I');
            $J = $get('J');
            $arcMode = $motion === 2 || $motion === 3;
            $hasMove = ! $nonMotion && ($X !== null || $Y !== null || ($arcMode && ($I !== null || $J !== null)));

            if ($hasMove) {
                if ($motion < 0) {
                    $warn($i, 'NO_MOTION_MODE', 'Coordinates given with no G00/G01/G02/G03 active');
                } else {
                    $nx = $inc ? $x + ($X ?? 0.0) : ($X ?? $x);
                    $ny = $inc ? $y + ($Y ?? 0.0) : ($Y ?? $y);
                    $type = $motion === 0 ? 'rapid' : ($torch ? ($comp !== 40 ? 'cut' : 'mark') : 'travel');
                    if ($motion === 0 && $torch) {
                        $warn($i, 'RAPID_TORCH_ON', 'Rapid (G00) move with the torch on');
                    }
                    $rr = $units === 'mm' ? $rapidRate * 25.4 : $rapidRate;
                    $segFeed = $motion === 0 ? $rr : $feed;

                    if (! $arcMode) {
                        $pts = [[$x, $y], [$nx, $ny]];
                        $len = hypot($nx - $x, $ny - $y);
                    } else {
                        $cx = $x + ($I ?? 0.0);
                        $cy = $y + ($J ?? 0.0);
                        $r0 = hypot($x - $cx, $y - $cy);
                        $r1 = hypot($nx - $cx, $ny - $cy);
                        if (abs($r0 - $r1) > ($units === 'mm' ? 0.05 : 0.002)) {
                            $warn($i, 'ARC_RADIUS', 'Arc start and end radius differ: '.Js::fixed($r0, 4).' vs '.Js::fixed($r1, 4));
                        }
                        $a0 = atan2($y - $cy, $x - $cx);
                        $a1 = atan2($ny - $cy, $nx - $cx);
                        $sw = $a1 - $a0;
                        if ($motion === 3) {
                            while ($sw <= 1e-9) {
                                $sw += 2 * M_PI;
                            }
                        } else {
                            while ($sw >= -1e-9) {
                                $sw -= 2 * M_PI;
                            }
                        }
                        $n = max(4, min(360, (int) ceil(abs($sw) / (M_PI / 36))));
                        $pts = [];
                        for ($k = 0; $k <= $n; $k++) {
                            $t = $k / $n;
                            $a = $a0 + $sw * $t;
                            $r = $r0 + ($r1 - $r0) * $t;
                            $pts[] = [$cx + $r * cos($a), $cy + $r * sin($a)];
                        }
                        $pts[$n] = [$nx, $ny];
                        $len = abs($sw) * ($r0 + $r1) / 2;
                    }

                    if ($type !== 'rapid' && ! ($segFeed > 0)) {
                        $warn($i, 'NO_FEED', 'Feed move with no feedrate (F) set');
                    }
                    $segments[] = [
                        'type' => $type,
                        'line' => $i,
                        'len' => $len,
                        'feed' => $segFeed,
                        'time' => $segFeed > 0 ? $len / $segFeed : 0.0,
                        'kerf' => $kerf,
                        'th' => $torch && $th,
                        'ar' => $torch && $ar,
                        'pts' => $pts,
                    ];
                    $x = $nx;
                    $y = $ny;
                }
            }
        }

        $last = max(0, count($lines) - 1);
        if (! $unitsSet) {
            $warn(0, 'NO_UNITS', 'No G20/G21 units code, so inches are assumed');
        }
        if (! $ended) {
            $warn($last, 'NO_END', 'No M02/M30 program end');
            if ($torch) {
                $warn($last, 'END_TORCH_ON', 'File ends with the torch on');
            }
        }

        $inf = ['minx' => INF, 'miny' => INF, 'maxx' => -INF, 'maxy' => -INF];
        $b = $inf;
        $cb = $inf;
        $tot = ['cut' => 0.0, 'mark' => 0.0, 'rapid' => 0.0, 'travel' => 0.0];
        $tim = $tot;
        $outLines = [];
        foreach ($segments as $s) {
            $tot[$s['type']] += $s['len'];
            $tim[$s['type']] += $s['time'];
            $isWork = $s['type'] === 'cut' || $s['type'] === 'mark';
            foreach ($s['pts'] as [$px, $py]) {
                $b = self::grow($b, $px, $py);
                if ($isWork) {
                    $cb = self::grow($cb, $px, $py);
                    if ($sheet !== null
                        && ($px < -0.01 || $py < -0.01 || $px > $sheet['w'] + 0.01 || $py > $sheet['h'] + 0.01)
                        && ! isset($outLines[$s['line']])
                        && count($outLines) < self::MAX_OFF_PLATE_LINES) {
                        $outLines[$s['line']] = true;
                        $warn($s['line'], 'OFF_PLATE', 'Cut runs off the plate edge');
                    }
                }
            }
        }
        usort($warnings, fn ($a, $b) => $a['line'] <=> $b['line']); // stable since PHP 8.0, like JS

        return new ParsedProgram(
            lines: $lines,
            segments: $segments,
            pierces: $pierces,
            warnings: $warnings,
            sheet: $sheet,
            material: $material,
            thickness: $thick,
            process: $process,
            dwellSeconds: $dwell,
            lengths: $tot,
            minutes: $tim,
            bounds: $b,
            cutBounds: $cb,
            units: $units,
        );
    }

    /** @param array{minx:float,miny:float,maxx:float,maxy:float} $b */
    private static function grow(array $b, float $x, float $y): array
    {
        if ($x < $b['minx']) {
            $b['minx'] = $x;
        }
        if ($y < $b['miny']) {
            $b['miny'] = $y;
        }
        if ($x > $b['maxx']) {
            $b['maxx'] = $x;
        }
        if ($y > $b['maxy']) {
            $b['maxy'] = $y;
        }

        return $b;
    }
}
