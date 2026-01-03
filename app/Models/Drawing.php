<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Drawing extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'number',
        'revision',
        'title',
        'file_path',
        'revised_at',
    ];

    protected $casts = [
        'revised_at' => 'datetime',
    ];

    public function getUrlAttribute(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        return route('drawings.show', $this->id);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assemblies(): HasMany
    {
        return $this->hasMany(Assembly::class);
    }
}
