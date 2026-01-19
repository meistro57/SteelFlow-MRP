<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $employee_id
 * @property int|null $project_id
 * @property int|null $assembly_id
 * @property int|null $part_work_area_id
 * @property int|null $work_area_id
 * @property int|null $batch_id
 * @property string|null $operation_code
 * @property \Illuminate\Support\Carbon|null $start_time
 * @property \Illuminate\Support\Carbon|null $end_time
 * @property float $hours
 * @property int $quantity
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Employee $employee
 * @property-read \App\Models\Project|null $project
 * @property-read \App\Models\PartWorkArea|null $partWorkArea
 * @property-read \App\Models\WorkArea|null $workArea
 */
class TimeEntry extends Model
{
    protected $fillable = [
        'employee_id',
        'project_id',
        'assembly_id',
        'part_work_area_id',
        'work_area_id',
        'batch_id',
        'operation_code',
        'start_time',
        'end_time',
        'hours',
        'quantity',
        'notes',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function partWorkArea(): BelongsTo
    {
        return $this->belongsTo(PartWorkArea::class);
    }

    public function workArea(): BelongsTo
    {
        return $this->belongsTo(WorkArea::class);
    }
}
