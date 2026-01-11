<?php

namespace Modules\UPF\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
