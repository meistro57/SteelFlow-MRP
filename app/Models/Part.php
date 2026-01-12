<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Modules\Core\Traits\HasAuditFields;
use Modules\Inventory\Models\Material;

/**
 * @property int $id
 * @property int $project_id
 * @property int|null $assembly_id
 * @property string $part_mark
 * @property int|null $material_id
 * @property string|null $type
 * @property string|null $size_imperial
 * @property string|null $size_metric
 * @property string|null $grade
 * @property float|null $length
 * @property int $quantity
 * @property bool $is_main_member
 * @property float|null $weight_each_lbs
 * @property float|null $weight_each_kg
 * @property float|null $total_weight_lbs
 * @property float|null $total_weight_kg
 * @property string|null $finish
 * @property string|null $remarks
 * @property string|null $file_number
 * @property string|null $unique_key
 * @property bool $nc_data_available
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Project $project
 * @property-read \App\Models\Assembly|null $assembly
 * @property-read \Modules\Inventory\Models\Material|null $material
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PartInstance[] $instances
 */
class Part extends Model
{
    use Searchable, SoftDeletes, HasAuditFields;

    protected $fillable = [
        'project_id',
        'assembly_id',
        'part_mark',
        'material_id',
        'upf_price_id',
        'type',
        'size_imperial',
        'size_metric',
        'grade',
        'length',
        'quantity',
        'is_main_member',
        'weight_each_lbs',
        'weight_each_kg',
        'total_weight_lbs',
        'total_weight_kg',
        'finish',
        'remarks',
        'file_number',
        'unique_key',
        'nc_data_available',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assembly(): BelongsTo
    {
        return $this->belongsTo(Assembly::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function upfPrice(): BelongsTo
    {
        return $this->belongsTo(\Modules\UPF\Models\UpfPrice::class);
    }

    public function instances(): HasMany
    {
        return $this->hasMany(PartInstance::class);
    }
}
