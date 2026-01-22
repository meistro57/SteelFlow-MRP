<?php

// MaintenanceEquipmentCategory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property string $default_criticality
 * @property string $default_usage_unit
 * @property int|null $default_interval_value
 * @property string|null $default_interval_unit
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MaintenanceEquipmentAsset> $assets
 */
class MaintenanceEquipmentCategory extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'default_criticality',
        'default_usage_unit',
        'default_interval_value',
        'default_interval_unit',
    ];

    public function assets(): HasMany
    {
        return $this->hasMany(MaintenanceEquipmentAsset::class, 'category_id');
    }
}
