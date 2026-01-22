<?php

// MaintenanceWorkOrderTask.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $work_order_id
 * @property string $description
 * @property bool $is_completed
 * @property string|null $completed_at
 * @property int|null $completed_by
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read MaintenanceWorkOrder $workOrder
 * @property-read User|null $completedBy
 */
class MaintenanceWorkOrderTask extends Model
{
    protected $fillable = [
        'work_order_id',
        'description',
        'is_completed',
        'completed_at',
        'completed_by',
        'notes',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(MaintenanceWorkOrder::class, 'work_order_id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
