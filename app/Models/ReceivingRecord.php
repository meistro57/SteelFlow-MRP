<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceivingRecord extends Model
{
    protected $fillable = [
        'purchase_order_line_id',
        'quantity_received',
        'receive_date',
        'heat_number',
        'mill_cert_number',
        'country_of_origin',
        'stock_area',
        'received_by',
        'notes',
    ];

    public function line(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderLine::class, 'purchase_order_line_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
