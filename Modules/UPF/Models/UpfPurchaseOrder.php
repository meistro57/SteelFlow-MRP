<?php

namespace Modules\UPF\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UpfPurchaseOrder extends Model
{
    use SoftDeletes;

    protected $table = 'upf_purchase_orders';

    protected $fillable = [
        'po_number',
        'po_date',
        'required_date',
        'promised_date',
        'vendor_code',
        'vendor_name',
        'vendor_address',
        'vendor_contact',
        'vendor_phone',
        'status',
        'status_code',
        'ship_via',
        'fob_point',
        'deliver_to',
        'subtotal',
        'tax_amount',
        'freight_amount',
        'total_amount',
        'payment_terms',
        'discount_days',
        'discount_percent',
        'created_by',
        'approved_by',
        'approved_at',
        'notes',
        'internal_notes',
    ];

    protected $casts = [
        'po_date' => 'date',
        'required_date' => 'date',
        'promised_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'freight_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(UpfPurchaseOrderItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
