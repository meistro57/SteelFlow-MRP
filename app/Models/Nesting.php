<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
