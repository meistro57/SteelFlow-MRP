<?php

// app/Models/GasBottleInspection.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Inventory\Models\StockItem;

class GasBottleInspection extends Model
{
    protected $fillable = [
        'stock_item_id',
        'scheduled_for',
        'completed_at',
        'outcome',
        'notes',
        'created_by',
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
