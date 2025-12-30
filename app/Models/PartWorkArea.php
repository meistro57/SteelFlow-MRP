<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartWorkArea extends Model
{
    protected $fillable = [
        'part_instance_id',
        'assembly_instance_id',
        'work_area_id',
        'batch_id',
        'sequence_number',
        'status',
        'estimated_hours',
        'actual_hours',
        'started_at',
        'completed_at',
        'completed_by',
        'notes',
    ];

    public function partInstance(): BelongsTo
    {
        return $this->belongsTo(PartInstance::class);
    }

    public function workArea(): BelongsTo
    {
        return $this->belongsTo(WorkArea::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class, 'batch_id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'completed_by');
    }
}
