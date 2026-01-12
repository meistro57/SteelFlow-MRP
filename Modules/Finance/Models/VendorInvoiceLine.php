<?php

namespace Modules\Finance\Models;

use App\Models\PurchaseOrderLine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property float $quantity
 * @property float $unit_price
 * @property float $extended_price
 * @property-read \App\Models\PurchaseOrderLine $poLine
 */
class VendorInvoiceLine extends Model
{
    protected $fillable = [
        'vendor_invoice_id',
        'purchase_order_line_id',
        'quantity',
        'unit_price',
        'extended_price',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'extended_price' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(VendorInvoice::class, 'vendor_invoice_id');
    }

    public function poLine(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderLine::class, 'purchase_order_line_id');
    }
}
