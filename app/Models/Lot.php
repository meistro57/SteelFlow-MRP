<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $project_id
 * @property int $phase_id
 * @property string $code
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $ship_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * 
 * @property-read \App\Models\Project $project
 * @property-read \App\Models\Phase $phase
 */
class Lot extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'phase_id',
        'code',
        'description',
        'ship_date',
    ];

    protected $casts = [
        'ship_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(Phase::class);
    }
}
