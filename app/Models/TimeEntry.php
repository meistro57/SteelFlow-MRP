<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
