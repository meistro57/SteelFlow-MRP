<?php

namespace Modules\UPF\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UpfPurchaseOrderItem extends Model
{
    use SoftDeletes;

    protected $table = 'upf_purchase_order_items';

    protected $fillable = [
        'upf_purchase_order_id',
        'line_number',
        'upf_price_id',
        'type',
        'size',
        'grade_name',
        'description',
        'quantity_ordered',
        'quantity_received',
        'quantity_remaining',
        'uom',
        'unit_price',
        'extended_price',
        'required_date',
        'promised_date',
        'job_number',
        'phase_code',
        'status',
        'last_received_date',
        'last_received_quantity',
    ];

    protected $casts = [
        'quantity_ordered' => 'decimal:4',
        'quantity_received' => 'decimal:4',
        'quantity_remaining' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'extended_price' => 'decimal:2',
        'required_date' => 'date',
        'promised_date' => 'date',
        'last_received_date' => 'date',
        'last_received_quantity' => 'decimal:4',
    ];

    public function upfPurchaseOrder(): BelongsTo
    {
        return $this->belongsTo(UpfPurchaseOrder::class);
    }

    public function upfPrice(): BelongsTo
    {
        return $this->belongsTo(UpfPrice::class);
    }
}
