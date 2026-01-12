<?php

namespace Modules\Finance\Models;

use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property float $scheduled_value
 */
class ScheduleOfValue extends Model
{
    protected $table = 'schedule_of_values';

    protected $fillable = [
        'project_id',
        'item_name',
        'description',
        'scheduled_value',
    ];

    protected $casts = [
        'scheduled_value' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
