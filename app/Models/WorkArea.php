<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkArea extends Model
{
    protected $fillable = [
        'department_id',
        'code',
        'name',
        'description',
        'sort_order',
        'is_active',
        'badge_barcode',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function routingSteps(): HasMany
    {
        return $this->hasMany(PartWorkArea::class);
    }
}
