<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $load_id
 * @property int $assembly_instance_id
 * @property float $weight_lbs
 * @property float $weight_kg
 * @property \Illuminate\Support\Carbon|null $loaded_at
 * @property int|null $loaded_by
 * @property \Illuminate\Support\Carbon|null $unloaded_at
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Load $truckLoad
 * @property-read \App\Models\AssemblyInstance $assemblyInstance
 */
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

    public function truckLoad(): BelongsTo
    {
        return $this->belongsTo(Load::class);
    }

    public function assemblyInstance(): BelongsTo
    {
        return $this->belongsTo(AssemblyInstance::class);
    }
}
