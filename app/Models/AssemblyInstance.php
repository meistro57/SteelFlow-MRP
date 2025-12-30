<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
