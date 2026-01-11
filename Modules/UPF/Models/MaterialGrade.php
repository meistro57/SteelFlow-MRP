<?php

namespace Modules\UPF\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $material_type_id
 * @property string|null $type
 * @property string $grade_name
 * @property string|null $description
 * @property string|null $pkey
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Modules\UPF\Models\MaterialType $materialType
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\UPF\Models\UpfPrice> $upfPrices
 */
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
