<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $part_id
 * @property int $assembly_instance_id
 * @property int $project_id
 * @property int|null $nesting_id
 * @property int|null $bar_number
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Part $part
 * @property-read \App\Models\AssemblyInstance $assemblyInstance
 * @property-read \App\Models\Project $project
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PartWorkArea> $routing
 */
class PartInstance extends Model
{
    protected $fillable = [
        'part_id',
        'assembly_instance_id',
        'project_id',
        'nesting_id',
        'bar_number',
        'status',
    ];

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public function assemblyInstance(): BelongsTo
    {
        return $this->belongsTo(AssemblyInstance::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function routing(): HasMany
    {
        return $this->hasMany(PartWorkArea::class);
    }
}
