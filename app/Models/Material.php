<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'size_imperial',
        'size_metric',
        'grade_id',
        'unit_weight_lbs',
        'unit_weight_kg',
        'price_per_lb',
        'price_per_kg',
        'surface_area_sqft',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'unit_weight_lbs' => 'decimal:4',
        'unit_weight_kg' => 'decimal:4',
        'price_per_lb' => 'decimal:4',
        'price_per_kg' => 'decimal:4',
        'surface_area_sqft' => 'decimal:4',
        'is_active' => 'boolean',
        'sort_order' => 'decimal:4',
    ];

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function parts(): HasMany
    {
        return $this->hasMany(Part::class);
    }

    public function stockItems(): HasMany
    {
        return $this->hasMany(StockItem::class);
    }
}
