<?php

declare(strict_types=1);

namespace Modules\CncData\Services;

use Illuminate\Support\Facades\DB;
use Modules\CncData\Engine\XprCutChart;
use Modules\CncData\Services\Support\RunStatistics;

/**
 * The rate book: what the shop's plasma actually does, per process, measured
 * from completed runs. This is the number the Estimating and Scheduling
 * modules should read — minutes per foot of cut and seconds per pierce — not
 * the cut-chart speed, which ignores IHS, retracts and pauses.
 */
class ProductionRateService
{
    /**
     * @param  array{machine_id?:?int,amps?:?int,material?:?string,thickness?:?float}  $filters
     * @return list<array<string,mixed>>
     */
    public function rateBook(array $filters = []): array
    {
        $cfg = config('cncdata.calibration');

        $rows = DB::table('cnc_runs as r')
            ->join('cnc_programs as p', 'p.id', '=', 'r.cnc_program_id')
            ->whereNull('r.deleted_at')
            ->whereNull('p.deleted_at')
            ->where('r.result', 'complete')
            ->where('r.est_minutes_at_run', '>', 0)
            ->when($filters['machine_id'] ?? null, fn ($q, $v) => $q->where('r.machine_id', $v))
            ->when($filters['amps'] ?? null, fn ($q, $v) => $q->where('p.amps', $v))
            ->when($filters['material'] ?? null, fn ($q, $v) => $q->where('p.material_name', $v))
            ->when($filters['thickness'] ?? null, fn ($q, $v) => $q->where('p.thickness', $v))
            ->get(['p.id as program_id', 'p.units', 'p.material_name', 'p.amps', 'p.thickness', 'p.cut_length', 'p.cut_pierces',
                'r.actual_minutes', 'r.est_minutes_at_run']);

        return $rows
            ->groupBy(fn ($r) => implode('|', [$r->units, $r->material_name, $r->amps, (float) $r->thickness]))
            ->map(function ($g) use ($cfg) {
                $f = $g->first();
                $ratios = $g->map(fn ($r) => $r->actual_minutes / $r->est_minutes_at_run)->values()->all();
                $median = RunStatistics::median($ratios);
                $spread = RunStatistics::spread($ratios);
                $fit = RunStatistics::fitCutAndPierce(
                    $g->map(fn ($r) => [(float) $r->cut_length, (float) $r->cut_pierces, (float) $r->actual_minutes])->values()->all(),
                );
                $perFoot = $f->units === 'mm' ? 304.8 : 12.0;
                $chart = $f->units === 'in' ? (XprCutChart::CHARTS[(int) $f->amps] ?? null) : null;
                $chartRow = $chart ? collect($chart['rows'])->first(fn ($row) => abs($row[0] - (float) $f->thickness) < 0.02) : null;

                return [
                    'units' => $f->units,
                    'material' => $f->material_name,
                    'amps' => (int) $f->amps,
                    'thickness' => (float) $f->thickness,
                    'programs' => $g->pluck('program_id')->unique()->count(),
                    'runs' => $g->count(),
                    'actual_vs_estimate' => $median !== null ? round($median, 3) : null,
                    'spread' => $spread !== null ? round($spread, 3) : null,
                    'validated' => $median !== null && $g->count() >= $cfg['min_runs'] && $spread !== null && $spread <= $cfg['max_spread'],
                    // From the fit, when cut length and pierce count vary enough across runs to separate them
                    'minutes_per_ft_cut' => $fit ? round($fit['per_length'] * $perFoot, 3) : null,
                    'seconds_per_pierce' => $fit ? round($fit['per_pierce'] * 60, 1) : null,
                    'fit_rmse_minutes' => $fit ? round($fit['rmse'], 2) : null,
                    'chart_speed' => $chartRow ? $chartRow[1] : null,
                ];
            })
            ->sortBy(['material', 'amps', 'thickness'])
            ->values()
            ->all();
    }
}
