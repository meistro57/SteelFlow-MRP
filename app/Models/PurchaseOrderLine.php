<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrderLine extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'line_number',
        'material_id',
        'type',
        'size',
        'grade',
        'length',
        'quantity',
        'quantity_received',
        'unit_price',
        'extended_price',
        'nesting_id',
        'notes',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function receivingRecords(): HasMany
    {
        return $this->hasMany(ReceivingRecord::class);
    }
}
