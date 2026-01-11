<?php

// app/Models/GasBottleRental.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\HasAuditFields;
use Modules\Inventory\Models\StockItem;

/**
 * @property int $id
 * @property int $stock_item_id
 * @property int $customer_id
 * @property string $status
 * @property string|null $reservation_expires_at
 * @property string|null $due_back_at
 * @property string|null $checked_out_at
 * @property string|null $returned_at
 * @property string|null $lost_at
 * @property string|null $damaged_at
 * @property int $deposit_cents
 * @property int $rental_rate_cents
 * @property string|null $rental_rate_period
 * @property string|null $notes
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read \Modules\Inventory\Models\StockItem $stockItem
 * @property-read \App\Models\Customer $customer
 * @property-read \App\Models\User|null $creator
 */
class GasBottleRental extends Model
{
    use HasAuditFields;

    protected $fillable = [
        'stock_item_id',
        'customer_id',
        'status',
        'reservation_expires_at',
        'due_back_at',
        'checked_out_at',
        'returned_at',
        'lost_at',
        'damaged_at',
        'deposit_cents',
        'rental_rate_cents',
        'rental_rate_period',
        'notes',
        'created_by',
        'updated_by',
    ];

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
