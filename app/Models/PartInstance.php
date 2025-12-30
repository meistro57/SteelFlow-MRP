<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
