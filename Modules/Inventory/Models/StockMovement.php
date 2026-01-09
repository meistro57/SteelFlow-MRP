<?php

namespace Modules\Inventory\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\HasAuditFields;

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
