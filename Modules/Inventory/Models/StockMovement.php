<?php

namespace Modules\Inventory\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\HasAuditFields;

/**
 * @property int $id
 * @property int $stock_item_id
 * @property string $movement_type
 * @property float $quantity
 * @property string $from_status
 * @property string $to_status
 * @property string|null $from_area
 * @property string|null $to_area
 * @property string|null $reference_type
 * @property string|null $reference_id
 * @property string|null $notes
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read \Modules\Inventory\Models\StockItem $stockItem
 * @property-read \App\Models\User|null $creator
 */
class StockMovement extends Model
{
    use HasAuditFields;

    protected $fillable = [
        'stock_item_id',
        'movement_type',
        'quantity',
        'from_status',
        'to_status',
        'from_area',
        'to_area',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
        'updated_by',
    ];

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
