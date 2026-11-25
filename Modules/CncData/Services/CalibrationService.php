<?php

declare(strict_types=1);

namespace Modules\CncData\Services;

use Modules\CncData\Enums\CncProgramStatus;
use Modules\CncData\Models\CncProgram;
use Modules\CncData\Models\CncRun;
use Modules\CncData\Services\Support\RunStatistics;

/**
 * The validation gate. Engine estimates are physics from the program; real
 * tables lose time to IHS, accel/decel, retracts and operator pauses. This
 * compares completed runs with what the engine predicted for the same process
 * (machine + current + thickness) and only marks an estimate "validated" once
 * enough runs agree. Costing and scheduling read plannedMinutes(), which
 * stays on the raw estimate until then.
 */
class CalibrationService
{
    /**
     * @return array{factor:?float,runs:int,spread:?float,validated:bool}
     */
    public function factorFor(?int $machineId, ?int $amps, ?float $thickness): array
    {
        $cfg = config('cncdata.calibration');

        $ratios = CncRun::query()
            ->where('result', 'complete')
            ->where('est_minutes_at_run', '>', 0)
            ->whereHas('program', function ($q) use ($amps, $thickness) {
                $q->where('amps', $amps)->where('thickness', $thickness);
            })
            ->when($machineId, fn ($q) => $q->where('machine_id', $machineId))
            ->latest('finished_at')
            ->latest('id')
            ->limit($cfg['window'])
            ->get(['actual_minutes', 'est_minutes_at_run'])
            ->map(fn (CncRun $r) => $r->actual_minutes / $r->est_minutes_at_run)
            ->values()
            ->all();

        $factor = RunStatistics::median($ratios);
        $spread = RunStatistics::spread($ratios);

        return [
            'factor' => $factor,
            'runs' => count($ratios),
            'spread' => $spread,
            'validated' => $factor !== null
                && count($ratios) >= $cfg['min_runs']
                && $spread !== null && $spread <= $cfg['max_spread'],
        ];
    }

    /** Re-apply the current factor to every live program in the same process group. */
    public function refreshGroup(?int $machineId, ?int $amps, ?float $thickness): array
    {
        $f = $this->factorFor($machineId, $amps, $thickness);

        CncProgram::query()
            ->where('amps', $amps)
            ->where('thickness', $thickness)
            ->when($machineId, fn ($q) => $q->where('machine_id', $machineId))
            ->where('status', '!=', CncProgramStatus::Archived->value)
            ->each(function (CncProgram $p) use ($f) {
                $p->forceFill([
                    'calibration_factor' => $f['factor'],
                    'est_minutes_calibrated' => $f['factor'] !== null ? round($p->est_minutes * $f['factor'], 4) : null,
                    'estimate_validated' => $f['validated'],
                ])->saveQuietly();
            });

        return $f;
    }

    public function refresh(CncProgram $program): array
    {
        return $this->refreshGroup($program->machine_id, $program->amps, $program->thickness);
    }
}
