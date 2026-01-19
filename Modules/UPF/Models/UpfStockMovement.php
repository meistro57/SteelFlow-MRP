<?php

namespace Modules\UPF\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UpfStockMovement extends Model
{
    protected $table = 'upf_stock_movements';

    protected $fillable = [
        'upf_stock_item_id',
        'transaction_type',
        'transaction_number',
        'transaction_date',
        'quantity',
        'quantity_before',
        'quantity_after',
        'unit_cost',
        'total_cost',
        'reference_type',
        'reference_id',
        'reference_number',
        'from_location',
        'to_location',
        'user_id',
        'notes',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'quantity' => 'decimal:4',
        'quantity_before' => 'decimal:4',
        'quantity_after' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:2',
    ];

    public function upfStockItem(): BelongsTo
    {
        return $this->belongsTo(UpfStockItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
