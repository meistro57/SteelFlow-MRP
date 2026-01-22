<?php

// MaintenanceWorkOrder.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $equipment_id
 * @property int|null $maintenance_program_id
 * @property int|null $fault_report_id
 * @property string $title
 * @property string|null $description
 * @property string $status
 * @property string $priority
 * @property int|null $requested_by
 * @property int|null $assigned_to
 * @property string|null $scheduled_date
 * @property string|null $due_date
 * @property string|null $started_at
 * @property string|null $completed_at
 * @property float|null $labour_hours
 * @property float|null $downtime_hours
 * @property string|null $location
 * @property float|null $meter_reading
 * @property string|null $completion_notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read MaintenanceEquipmentAsset $equipment
 * @property-read MaintenanceProgram|null $maintenanceProgram
 * @property-read MaintenanceFaultReport|null $faultReport
 * @property-read User|null $requestedBy
 * @property-read User|null $assignedTo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MaintenanceWorkOrderTask> $tasks
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MaintenancePartUsage> $partUsages
 */
class MaintenanceWorkOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'equipment_id',
        'maintenance_program_id',
        'fault_report_id',
        'title',
        'description',
        'status',
        'priority',
        'requested_by',
        'assigned_to',
        'scheduled_date',
        'due_date',
        'started_at',
        'completed_at',
        'labour_hours',
        'downtime_hours',
        'location',
        'meter_reading',
        'completion_notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'due_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'labour_hours' => 'decimal:2',
        'downtime_hours' => 'decimal:2',
        'meter_reading' => 'decimal:2',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(MaintenanceEquipmentAsset::class, 'equipment_id');
    }

    public function maintenanceProgram(): BelongsTo
    {
        return $this->belongsTo(MaintenanceProgram::class, 'maintenance_program_id');
    }

    public function faultReport(): BelongsTo
    {
        return $this->belongsTo(MaintenanceFaultReport::class, 'fault_report_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(MaintenanceWorkOrderTask::class, 'work_order_id');
    }

    public function partUsages(): HasMany
    {
        return $this->hasMany(MaintenancePartUsage::class, 'work_order_id');
    }
}
