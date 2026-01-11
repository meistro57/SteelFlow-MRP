<?php

namespace Modules\Inventory\Models;

use App\Models\PurchaseOrderLine;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $purchase_order_line_id
 * @property float $quantity_received
 * @property \Illuminate\Support\Carbon|null $receive_date
 * @property string|null $heat_number
 * @property string|null $mill_cert_number
 * @property string|null $country_of_origin
 * @property string|null $stock_area
 * @property int|null $received_by
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read \App\Models\PurchaseOrderLine $line
 * @property-read \App\Models\User|null $receiver
 */
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
