<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockItem extends Model
{
    protected $fillable = [
        'stock_id',
        'material_id',
        'type',
        'size',
        'grade',
        'length',
        'quantity',
        'status',
        'reserved_project_id',
        'stock_area',
        'heat_number',
        'po_number',
        'country_of_origin',
        'cost_per_unit',
        'receive_date',
        'notes',
        'is_remnant',
        'parent_stock_id',
    ];

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function reservedProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'reserved_project_id');
    }

    public function parentStock(): BelongsTo
    {
        return $this->belongsTo(StockItem::class, 'parent_stock_id');
    }

    public function remnants(): HasMany
    {
        return $this->hasMany(StockItem::class, 'parent_stock_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
