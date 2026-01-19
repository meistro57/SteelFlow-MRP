<?php

namespace Modules\UPF\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $material_type_id
 * @property int|null $material_grade_id
 * @property string $type
 * @property string $size
 * @property string $grade_name
 * @property string|null $pkey
 * @property int $filekey
 * @property int $orderkey
 * @property string|null $size_description
 * @property float|null $nominal_thickness
 * @property float|null $nominal_width
 * @property float|null $nominal_length
 * @property float|null $weight_per_foot
 * @property float|null $weight_per_unit
 * @property float $unit_price
 * @property string $price_unit
 * @property float|null $minimum_charge
 * @property string|null $vendor_part_number
 * @property string|null $preferred_vendor
 * @property float|null $lead_time_days
 * @property float|null $min_stock_level
 * @property float|null $max_stock_level
 * @property float|null $reorder_point
 * @property bool $is_active
 * @property bool $is_stocked
 * @property bool $allow_fabrication
 * @property array|null $specifications
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Modules\UPF\Models\MaterialType $materialType
 * @property-read \Modules\UPF\Models\MaterialGrade|null $materialGrade
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\UPF\Models\UpfStockItem> $upfStockItems
 */
class UpfPrice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'material_type_id',
        'material_grade_id',
        'type',
        'size',
        'grade_name',
        'pkey',
        'filekey',
        'orderkey',
        'size_description',
        'nominal_thickness',
        'nominal_width',
        'nominal_length',
        'weight_per_foot',
        'weight_per_unit',
        'unit_price',
        'price_unit',
        'minimum_charge',
        'vendor_part_number',
        'preferred_vendor',
        'lead_time_days',
        'min_stock_level',
        'max_stock_level',
        'reorder_point',
        'is_active',
        'is_stocked',
        'allow_fabrication',
        'specifications',
        'notes',
    ];

    protected $casts = [
        'filekey' => 'integer',
        'orderkey' => 'integer',
        'nominal_thickness' => 'decimal:4',
        'nominal_width' => 'decimal:4',
        'nominal_length' => 'decimal:4',
        'weight_per_foot' => 'decimal:4',
        'weight_per_unit' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'minimum_charge' => 'decimal:2',
        'lead_time_days' => 'decimal:2',
        'min_stock_level' => 'decimal:4',
        'max_stock_level' => 'decimal:4',
        'reorder_point' => 'decimal:4',
        'is_active' => 'boolean',
        'is_stocked' => 'boolean',
        'allow_fabrication' => 'boolean',
        'specifications' => 'json',
    ];

    public function materialType(): BelongsTo
    {
        return $this->belongsTo(MaterialType::class);
    }

    public function materialGrade(): BelongsTo
    {
        return $this->belongsTo(MaterialGrade::class);
    }

    public function upfStockItems(): HasMany
    {
        return $this->hasMany(UpfStockItem::class);
    }

    public function upfPurchaseOrderItems(): HasMany
    {
        return $this->hasMany(UpfPurchaseOrderItem::class);
    }
}
