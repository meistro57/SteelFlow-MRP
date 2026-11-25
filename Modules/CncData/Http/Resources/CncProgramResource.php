<?php

declare(strict_types=1);

namespace Modules\CncData\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\CncData\Models\CncProgram;

/** @mixin CncProgram */
class CncProgramResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'program_name' => $this->program_name,
            'status' => $this->status->value,
            'project_id' => $this->project_id,
            'machine_id' => $this->machine_id,
            'file_hash' => $this->file_hash,
            'material' => ['name' => $this->material_name, 'thickness' => $this->thickness, 'sheet_x' => $this->sheet_x, 'sheet_y' => $this->sheet_y],
            'process' => ['label' => $this->process_label, 'amps' => $this->amps],
            'units' => $this->units,
            'lengths' => ['cut' => $this->cut_length, 'mark' => $this->mark_length, 'travel' => $this->travel_length, 'rapid' => $this->rapid_length],
            'pierces' => ['cut' => $this->cut_pierces, 'true_hole' => $this->true_hole_pierces, 'mark_starts' => $this->mark_starts],
            'time' => [
                'est_minutes' => $this->est_minutes,
                'arc_minutes' => $this->arc_minutes,
                'calibration_factor' => $this->calibration_factor,
                'est_minutes_calibrated' => $this->est_minutes_calibrated,
                'validated' => $this->estimate_validated,
                'planned_minutes' => round($this->plannedMinutes(), 4),
            ],
            'checks' => [
                'errors' => $this->error_count,
                'warnings' => $this->warning_count,
                'chart_feed_ok' => $this->chart_feed_ok,
                'chart_kerf_ok' => $this->chart_kerf_ok,
                'items' => $this->warnings(),
            ],
            'analysis' => $this->when($request->boolean('full'), fn () => $this->analysis),
            'parts' => $this->whenLoaded('parts', fn () => $this->parts->map->only(['id', 'piece_mark', 'quantity', 'part_id', 'assembly_id', 'source'])),
            'runs' => $this->whenLoaded('runs', fn () => $this->runs->map->only(['id', 'actual_minutes', 'est_minutes_at_run', 'variance_pct', 'result', 'source', 'finished_at'])),
            'analyzed_at' => $this->analyzed_at?->toIso8601String(),
        ];
    }
}
