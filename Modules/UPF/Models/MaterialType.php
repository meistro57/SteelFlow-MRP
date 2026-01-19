<?php

namespace Modules\UPF\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $type
 * @property string $title
 * @property string|null $description
 * @property string|null $pkey
 * @property bool $is_active
 * @property bool $is_metric
 * @property int|null $display_order
 * @property array|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\UPF\Models\MaterialGrade> $materialGrades
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\UPF\Models\UpfPrice> $upfPrices
 */
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
