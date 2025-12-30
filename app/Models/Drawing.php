<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Drawing extends Model
{
    protected $fillable = [
        'project_id',
        'number',
        'revision',
        'title',
        'file_path',
        'revised_at',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assemblies(): HasMany
    {
        return $this->hasMany(Assembly::class);
    }
}
