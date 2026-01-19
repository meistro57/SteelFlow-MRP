<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $nesting_number
 * @property string $type
 * @property int $project_id
 * @property string|null $material_type
 * @property string|null $grade
 * @property string $status
 * @property int $total_bars
 * @property int $total_parts
 * @property float $total_length
 * @property float $used_length
 * @property float $waste_length
 * @property float $efficiency_percent
 * @property float $kerf_allowance
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property int|null $verified_by
 * @property \Illuminate\Support\Carbon|null $verified_at
 * @property int|null $confirmed_by
 * @property \Illuminate\Support\Carbon|null $confirmed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Project $project
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\NestingBar> $bars
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\NestingPart> $parts
 * @property-read \App\Models\User|null $approver
 */
class Nesting extends Model
{
    protected $fillable = [
        'nesting_number',
        'type',
        'project_id',
        'material_type',
        'grade',
        'status',
        'total_bars',
        'total_parts',
        'total_length',
        'used_length',
        'waste_length',
        'efficiency_percent',
        'kerf_allowance',
        'approved_by',
        'approved_at',
        'verified_by',
        'verified_at',
        'confirmed_by',
        'confirmed_at',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function bars(): HasMany
    {
        return $this->hasMany(NestingBar::class);
    }

    public function parts(): HasMany
    {
        return $this->hasMany(NestingPart::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
