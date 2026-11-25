<?php

declare(strict_types=1);

namespace Modules\CncData\Services;

use Modules\CncData\Models\CncProcessRate;
use Modules\CncData\Models\CncProgram;

/**
 * Consumable and machine cost for a program, from the shop's own rates.
 *
 *   consumables = cut pierces × cost/pierce + arc-on minutes × cost/arc-minute
 *   machine     = planned minutes ÷ 60 × machine rate/hour
 *
 * Costs are labelled provisional until the time estimate is validated by
 * actual runs (see CalibrationService); estimating should not quote off a
 * provisional figure without saying so.
 */
class CncCostService
{
    /** @return array<string,mixed> */
    public function estimate(CncProgram $program): array
    {
        $rate = $this->rateFor($program);
        if (! $rate) {
            return [
                'available' => false,
                'reason' => "No cost rate set up for {$program->amps} A {$program->material_name}".($program->thickness ? " {$program->thickness}\"" : '').'.',
            ];
        }

        $planned = $program->plannedMinutes();
        // Arc-on time scales with the same factor as the whole run.
        $arc = $program->estimate_validated && $program->calibration_factor
            ? $program->arc_minutes * $program->calibration_factor
            : $program->arc_minutes;

        $pierce = $program->cut_pierces * $rate->cost_per_pierce;
        $arcCost = $arc * $rate->cost_per_arc_minute;
        $machine = $planned / 60 * $rate->machine_rate_per_hour;

        return [
            'available' => true,
            'validated' => $program->estimate_validated,
            'basis' => $program->estimate_validated ? 'calibrated against actual runs' : 'engine estimate, not yet validated',
            'rate_id' => $rate->id,
            'planned_minutes' => round($planned, 2),
            'arc_minutes' => round($arc, 2),
            'pierce_cost' => round($pierce, 2),
            'arc_cost' => round($arcCost, 2),
            'consumable_cost' => round($pierce + $arcCost, 2),
            'machine_cost' => round($machine, 2),
            'total' => round($pierce + $arcCost + $machine, 2),
        ];
    }

    /** Most specific rate wins: machine over any machine, exact thickness over any thickness, newest first. */
    public function rateFor(CncProgram $program): ?CncProcessRate
    {
        if (! $program->amps) {
            return null;
        }

        return CncProcessRate::query()
            ->where('amps', $program->amps)
            ->whereRaw('LOWER(material) = ?', [strtolower((string) $program->material_name)])
            ->where(fn ($q) => $q->whereNull('machine_id')->orWhere('machine_id', $program->machine_id))
            ->where(fn ($q) => $q->whereNull('thickness')->orWhere('thickness', $program->thickness))
            ->where(fn ($q) => $q->whereNull('effective_from')->orWhere('effective_from', '<=', now()->toDateString()))
            ->orderByRaw('machine_id IS NULL')
            ->orderByRaw('thickness IS NULL')
            ->orderByDesc('effective_from')
            ->first();
    }
}
