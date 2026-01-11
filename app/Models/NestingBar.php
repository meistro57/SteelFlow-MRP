<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Inventory\Models\StockItem;

/**
 * @property int $id
 * @property int $nesting_id
 * @property int $bar_number
 * @property int|null $stock_item_id
 * @property bool $is_purchase
 * @property float $length
 * @property int $quantity
 * @property float $used_length
 * @property float $remnant_length
 * @property bool $is_cut
 * @property string|null $cut_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Nesting $nesting
 * @property-read \Modules\Inventory\Models\StockItem|null $stockItem
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\NestingPart> $nestingParts
 */
class NestingBar extends Model
{
    protected $fillable = [
        'nesting_id',
        'bar_number',
        'stock_item_id',
        'is_purchase',
        'length',
        'quantity',
        'used_length',
        'remnant_length',
        'is_cut',
        'cut_date',
    ];

    public function nesting(): BelongsTo
    {
        return $this->belongsTo(Nesting::class);
    }

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class);
    }

    public function nestingParts(): HasMany
    {
        return $this->hasMany(NestingPart::class);
    }
}
