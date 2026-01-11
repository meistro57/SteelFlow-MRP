<?php

namespace Modules\Inventory\Models;

use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string|null $stock_id
 * @property int $material_id
 * @property string $type
 * @property string $size
 * @property string $grade
 * @property float $length
 * @property int $quantity
 * @property string $status
 * @property int|null $reserved_project_id
 * @property string|null $stock_area
 * @property string|null $heat_number
 * @property string|null $po_number
 * @property string|null $country_of_origin
 * @property float|null $cost_per_unit
 * @property \Illuminate\Support\Carbon|null $receive_date
 * @property string|null $notes
 * @property bool $is_remnant
 * @property int|null $parent_stock_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Modules\Inventory\Models\Material $material
 * @property-read \App\Models\Project|null $reservedProject
 * @property-read \Modules\Inventory\Models\StockItem|null $parentStock
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Inventory\Models\StockItem> $remnants
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Inventory\Models\StockMovement> $movements
 * @property-read float|null $total_length_in
 * @property float|null $total_length
 */
class StockItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'stock_id',
        'material_id',
        'upf_price_id',
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

    public function upfPrice(): BelongsTo
    {
        return $this->belongsTo(\Modules\UPF\Models\UpfPrice::class);
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
