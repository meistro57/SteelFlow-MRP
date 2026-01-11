<?php

// app/Models/GasBottleInspection.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\HasAuditFields;
use Modules\Inventory\Models\StockItem;

/**
 * @property int $id
 * @property int $stock_item_id
 * @property string|null $scheduled_for
 * @property string|null $completed_at
 * @property string|null $outcome
 * @property string|null $notes
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read \Modules\Inventory\Models\StockItem $stockItem
 * @property-read \App\Models\User|null $creator
 */
class GasBottleInspection extends Model
{
    use HasAuditFields;

    protected $fillable = [
        'stock_item_id',
        'scheduled_for',
        'completed_at',
        'outcome',
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
