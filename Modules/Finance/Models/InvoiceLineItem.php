<?php

namespace Modules\Finance\Models;

use App\Models\Load;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $invoice_id
 * @property int|null $load_id
 * @property int|null $sov_id
 * @property string $description
 * @property float $quantity
 * @property float $unit_price
 * @property float $tax_rate
 * @property float $line_total
 * @property-read \Modules\Finance\Models\Invoice $invoice
 * @property-read \App\Models\Load|null $shippingLoad
 * @property-read \Modules\Finance\Models\ScheduleOfValue|null $sov
 */
class InvoiceLineItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'load_id',
        'sov_id',
        'description',
        'quantity',
        'unit_price',
        'tax_rate',
        'line_total',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function shippingLoad(): BelongsTo
    {
        return $this->belongsTo(Load::class, 'load_id');
    }

    public function sov(): BelongsTo
    {
        return $this->belongsTo(ScheduleOfValue::class, 'sov_id');
    }
}
