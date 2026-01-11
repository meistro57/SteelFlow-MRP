<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $assembly_id
 * @property int $project_id
 * @property int|null $phase_id
 * @property int|null $lot_id
 * @property int|null $batch_id
 * @property int $instance_number
 * @property string $status
 * @property int|null $load_id
 * @property float|null $ship_weight_lbs
 * @property float|null $ship_weight_kg
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $shipped_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Assembly $assembly
 * @property-read \App\Models\Project $project
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PartInstance> $partInstances
 */
class AssemblyInstance extends Model
{
    protected $fillable = [
        'assembly_id',
        'project_id',
        'phase_id',
        'lot_id',
        'batch_id',
        'instance_number',
        'status',
        'load_id',
        'ship_weight_lbs',
        'ship_weight_kg',
        'completed_at',
        'shipped_at',
    ];

    public function assembly(): BelongsTo
    {
        return $this->belongsTo(Assembly::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function partInstances(): HasMany
    {
        return $this->hasMany(PartInstance::class);
    }
}
