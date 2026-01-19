<?php

namespace Modules\Inventory\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $cycle_count_id
 * @property int $stock_item_id
 * @property int $expected_quantity
 * @property int|null $counted_quantity
 * @property bool $is_counted
 * @property int|null $counted_by
 * @property \Illuminate\Support\Carbon|null $counted_at
 * @property bool $is_reconciled
 * @property string|null $variance_reason
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\Inventory\Models\CycleCount $cycleCount
 * @property-read \Modules\Inventory\Models\StockItem $stockItem
 * @property-read \App\Models\User|null $countedByUser
 */
class CycleCountLine extends Model
{
    protected $fillable = [
        'cycle_count_id',
        'stock_item_id',
        'expected_quantity',
        'counted_quantity',
        'is_counted',
        'counted_by',
        'counted_at',
        'is_reconciled',
        'variance_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_counted' => 'boolean',
            'is_reconciled' => 'boolean',
            'counted_at' => 'datetime',
        ];
    }

    public function cycleCount(): BelongsTo
    {
        return $this->belongsTo(CycleCount::class);
    }

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class);
    }

    public function countedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counted_by');
    }

    /**
     * Get the variance (difference between counted and expected).
     */
    public function getVarianceAttribute(): ?int
    {
        if ($this->counted_quantity === null) {
            return null;
        }

        return $this->counted_quantity - $this->expected_quantity;
    }

    /**
     * Check if there is a variance.
     */
    public function getHasVarianceAttribute(): bool
    {
        if ($this->counted_quantity === null) {
            return false;
        }

        return $this->counted_quantity !== $this->expected_quantity;
    }
}
