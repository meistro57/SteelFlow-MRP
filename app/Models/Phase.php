<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $project_id
 * @property string $code
 * @property string|null $description
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * 
 * @property-read \App\Models\Project $project
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Lot> $lots
 */
class Phase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'code',
        'description',
        'sort_order',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function lots(): HasMany
    {
        return $this->hasMany(Lot::class);
    }
}
