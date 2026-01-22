<?php

// MaintenancePartUsage.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $work_order_id
 * @property int $part_id
 * @property int $quantity
 * @property float $unit_cost
 * @property float $cost_total
 * @property string|null $used_at
 * @property string|null $notes
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read MaintenanceWorkOrder $workOrder
 * @property-read MaintenancePart $part
 * @property-read User|null $createdBy
 */
class MaintenancePartUsage extends Model
{
    protected $fillable = [
        'work_order_id',
        'part_id',
        'quantity',
        'unit_cost',
        'cost_total',
        'used_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'cost_total' => 'decimal:2',
        'used_at' => 'datetime',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(MaintenanceWorkOrder::class, 'work_order_id');
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(MaintenancePart::class, 'part_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
