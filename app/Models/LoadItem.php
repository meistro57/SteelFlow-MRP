<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoadItem extends Model
{
    protected $fillable = [
        'load_id',
        'assembly_instance_id',
        'weight_lbs',
        'weight_kg',
        'loaded_at',
        'loaded_by',
        'unloaded_at',
        'notes',
    ];

    public function load(): BelongsTo
    {
        return $this->belongsTo(Load::class);
    }

    public function assemblyInstance(): BelongsTo
    {
        return $this->belongsTo(AssemblyInstance::class);
    }
}
