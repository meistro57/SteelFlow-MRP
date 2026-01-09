<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Traits\HasAuditFields;
use Modules\Inventory\Models\Vendor;

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
