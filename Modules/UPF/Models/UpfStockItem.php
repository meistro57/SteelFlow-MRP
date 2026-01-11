<?php

namespace Modules\UPF\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $upf_price_id
 * @property string $type
 * @property string $size
 * @property string $grade_name
 * @property string|null $location
 * @property string|null $bin_location
 * @property float $quantity_on_hand
 * @property float $quantity_available
 * @property float $quantity_allocated
 * @property float $quantity_on_order
 * @property string $uom
 * @property float $unit_cost
 * @property float $total_value
 * @property string $cost_method
 * @property \Illuminate\Support\Carbon|null $last_received_date
 * @property \Illuminate\Support\Carbon|null $last_issued_date
 * @property int|null $turnover_days
 * @property bool $is_active
 * @property bool $allow_negative
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Modules\UPF\Models\UpfPrice $upfPrice
 */
class UpfStockItem extends Model
{
    use SoftDeletes;

    protected $table = 'upf_stock_items';

    protected $fillable = [
        'upf_price_id',
        'type',
        'size',
        'grade_name',
        'location',
        'bin_location',
        'quantity_on_hand',
        'quantity_available',
        'quantity_allocated',
        'quantity_on_order',
        'uom',
        'unit_cost',
        'total_value',
        'cost_method',
        'last_received_date',
        'last_issued_date',
        'turnover_days',
        'is_active',
        'allow_negative',
    ];

    protected $casts = [
        'quantity_on_hand' => 'decimal:4',
        'quantity_available' => 'decimal:4',
        'quantity_allocated' => 'decimal:4',
        'quantity_on_order' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'total_value' => 'decimal:2',
        'is_active' => 'boolean',
        'allow_negative' => 'boolean',
        'last_received_date' => 'date',
        'last_issued_date' => 'date',
    ];

    public function upfPrice(): BelongsTo
    {
        return $this->belongsTo(UpfPrice::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(UpfStockMovement::class);
    }
}
