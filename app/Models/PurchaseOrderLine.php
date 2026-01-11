<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Inventory\Models\Material;
use Modules\Inventory\Models\ReceivingRecord;

/**
 * @property int $id
 * @property int $purchase_order_id
 * @property int $line_number
 * @property int|null $material_id
 * @property string|null $type
 * @property string|null $size
 * @property string|null $grade
 * @property float|null $length
 * @property float $quantity
 * @property float $quantity_received
 * @property float $unit_price
 * @property float $extended_price
 * @property int|null $nesting_id
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\PurchaseOrder $purchaseOrder
 * @property-read \Modules\Inventory\Models\Material|null $material
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Inventory\Models\ReceivingRecord> $receivingRecords
 */
class PurchaseOrderLine extends Model
{
    use SoftDeletes;

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
