<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
