<?php

// MaintenanceFaultReport.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $equipment_id
 * @property int|null $reported_by
 * @property string $reported_at
 * @property string $severity
 * @property string $description
 * @property string|null $immediate_action
 * @property string $status
 * @property string|null $resolution_notes
 * @property string|null $resolved_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read MaintenanceEquipmentAsset $equipment
 * @property-read User|null $reportedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MaintenanceWorkOrder> $workOrders
 */
class MaintenanceFaultReport extends Model
{
    protected $fillable = [
        'equipment_id',
        'reported_by',
        'reported_at',
        'severity',
        'description',
        'immediate_action',
        'status',
        'resolution_notes',
        'resolved_at',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(MaintenanceEquipmentAsset::class, 'equipment_id');
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(MaintenanceWorkOrder::class, 'fault_report_id');
    }
}
