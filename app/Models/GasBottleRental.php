<?php

// app/Models/GasBottleRental.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GasBottleRental extends Model
{
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
