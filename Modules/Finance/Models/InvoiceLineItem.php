<?php

namespace Modules\Finance\Models;

use App\Models\Load;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function load(): BelongsTo
    {
        return $this->belongsTo(Load::class);
    }

    public function sov(): BelongsTo
    {
        return $this->belongsTo(ScheduleOfValue::class, 'sov_id');
    }
}
