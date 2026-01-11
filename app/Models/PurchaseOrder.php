<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Traits\HasAuditFields;
use Modules\Inventory\Models\Vendor;

/**
 * @property int $id
 * @property string $po_number
 * @property int $vendor_id
 * @property int|null $project_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $order_date
 * @property \Illuminate\Support\Carbon|null $expected_date
 * @property string|null $ship_to_address
 * @property float $subtotal
 * @property float $tax
 * @property float $freight
 * @property float $total
 * @property string|null $notes
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Modules\Inventory\Models\Vendor $vendor
 * @property-read \App\Models\Project|null $project
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchaseOrderLine> $lines
 * @property-read \App\Models\User|null $creator
 */
class PurchaseOrder extends Model
{
    use HasAuditFields;
    use SoftDeletes;

    protected $fillable = [
        'po_number',
        'vendor_id',
        'project_id',
        'status',
        'order_date',
        'expected_date',
        'ship_to_address',
        'subtotal',
        'tax',
        'freight',
        'total',
        'notes',
        'created_by',
        'updated_by',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PurchaseOrderLine::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
