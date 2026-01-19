<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
/**
 * @property int $id
 * @property int $project_id
 * @property string $number
 * @property string $revision
 * @property string|null $title
 * @property string|null $file_path
 * @property \Illuminate\Support\Carbon|null $revised_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read string|null $url
 * @property-read \App\Models\Project $project
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Assembly> $assemblies
 */
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Documents\Models\Document;

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

    /**
     * Get all of the drawing's documents.
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    protected $appends = ['url'];

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
