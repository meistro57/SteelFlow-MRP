<?php

namespace Modules\UPF\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type',
        'title',
        'description',
        'pkey',
        'is_active',
        'is_metric',
        'display_order',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function materialGrades(): HasMany
    {
        return $this->hasMany(MaterialGrade::class);
    }

    public function upfPrices(): HasMany
    {
        return $this->hasMany(UpfPrice::class);
    }
}
