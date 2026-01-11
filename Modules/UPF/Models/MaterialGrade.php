<?php

namespace Modules\UPF\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialGrade extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'material_type_id',
        'type',
        'grade_name',
        'description',
        'pkey',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function materialType(): BelongsTo
    {
        return $this->belongsTo(MaterialType::class);
    }

    public function upfPrices(): HasMany
    {
        return $this->hasMany(UpfPrice::class);
    }
}
