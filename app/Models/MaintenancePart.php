<?php

// MaintenancePart.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int|null $vendor_id
 * @property string $name
 * @property string|null $part_number
 * @property string|null $category
 * @property float $unit_cost
 * @property int $quantity_on_hand
 * @property int|null $reorder_point
 * @property int|null $reorder_qty
 * @property int|null $lead_time_days
 * @property string|null $inventory_location
 * @property string|null $last_stocktake_at
 * @property string|null $notes
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read MaintenanceVendor|null $vendor
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MaintenancePartUsage> $partUsages
 */
class MaintenancePart extends Model
{
    protected $fillable = [
        'vendor_id',
        'name',
        'part_number',
        'category',
        'unit_cost',
        'quantity_on_hand',
        'reorder_point',
        'reorder_qty',
        'lead_time_days',
        'inventory_location',
        'last_stocktake_at',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'quantity_on_hand' => 'integer',
        'last_stocktake_at' => 'date',
        'is_active' => 'boolean',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(MaintenanceVendor::class, 'vendor_id');
    }

    public function partUsages(): HasMany
    {
        return $this->hasMany(MaintenancePartUsage::class, 'part_id');
    }
}
