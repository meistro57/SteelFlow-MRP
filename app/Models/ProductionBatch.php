<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $batch_number
 * @property int $project_id
 * @property string|null $description
 * @property string $status
 * @property int $priority
 * @property \Illuminate\Support\Carbon|null $released_at
 * @property int|null $released_by
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Project $project
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PartWorkArea> $routingSteps
 * @property-read \App\Models\User|null $releaser
 */
class ProductionBatch extends Model
{
    protected $fillable = [
        'batch_number',
        'project_id',
        'description',
        'status',
        'priority',
        'released_at',
        'released_by',
        'completed_at',
        'notes',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function routingSteps(): HasMany
    {
        return $this->hasMany(PartWorkArea::class, 'batch_id');
    }

    public function releaser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }
}
