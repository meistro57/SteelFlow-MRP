<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Traits\HasAuditFields;
use Modules\Documents\Models\Document;

class VendorInvoice extends Model
{
    use SoftDeletes, HasAuditFields;

    protected $fillable = [
        'purchase_order_id',
        'invoice_number',
        'amount',
        'invoice_date',
        'match_status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'invoice_date' => 'date',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get all of the invoice's documents (e.g. PDF of the invoice itself).
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
