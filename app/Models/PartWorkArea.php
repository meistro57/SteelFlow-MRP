<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $part_instance_id
 * @property int|null $assembly_instance_id
 * @property int $work_area_id
 * @property int|null $batch_id
 * @property int $sequence_number
 * @property string $status
 * @property float|null $estimated_hours
 * @property float|null $actual_hours
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property int|null $completed_by
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read \App\Models\PartInstance $partInstance
 * @property-read \App\Models\WorkArea $workArea
 * @property-read \App\Models\ProductionBatch|null $batch
 * @property-read \App\Models\Employee|null $completedBy
 */
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
