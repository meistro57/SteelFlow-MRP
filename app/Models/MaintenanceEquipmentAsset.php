<?php

// MaintenanceEquipmentAsset.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string|null $asset_tag
 * @property string|null $serial_number
 * @property string|null $manufacturer
 * @property string|null $model
 * @property int|null $year
 * @property string|null $purchase_date
 * @property string|null $in_service_date
 * @property string $status
 * @property string|null $location
 * @property string|null $department
 * @property string $criticality
 * @property string $usage_unit
 * @property float $current_usage
 * @property float|null $next_service_due_usage
 * @property string|null $next_service_due_date
 * @property string|null $warranty_expiry
 * @property string|null $last_inspection_date
 * @property int|null $responsible_user_id
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read MaintenanceEquipmentCategory $category
 * @property-read User|null $responsibleUser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MaintenanceUsageLog> $usageLogs
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MaintenanceProgram> $maintenancePrograms
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MaintenanceInspection> $inspections
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MaintenanceFaultReport> $faultReports
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MaintenanceWorkOrder> $workOrders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MaintenanceComplianceRecord> $complianceRecords
 */
class MaintenanceEquipmentAsset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'asset_tag',
        'serial_number',
        'manufacturer',
        'model',
        'year',
        'purchase_date',
        'in_service_date',
        'status',
        'location',
        'department',
        'criticality',
        'usage_unit',
        'current_usage',
        'next_service_due_usage',
        'next_service_due_date',
        'warranty_expiry',
        'last_inspection_date',
        'responsible_user_id',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'in_service_date' => 'date',
        'next_service_due_date' => 'date',
        'warranty_expiry' => 'date',
        'last_inspection_date' => 'date',
        'current_usage' => 'decimal:2',
        'next_service_due_usage' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(MaintenanceEquipmentCategory::class, 'category_id');
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function usageLogs(): HasMany
    {
        return $this->hasMany(MaintenanceUsageLog::class, 'equipment_id');
    }

    public function maintenancePrograms(): HasMany
    {
        return $this->hasMany(MaintenanceProgram::class, 'equipment_id');
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(MaintenanceInspection::class, 'equipment_id');
    }

    public function faultReports(): HasMany
    {
        return $this->hasMany(MaintenanceFaultReport::class, 'equipment_id');
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(MaintenanceWorkOrder::class, 'equipment_id');
    }

    public function complianceRecords(): HasMany
    {
        return $this->hasMany(MaintenanceComplianceRecord::class, 'equipment_id');
    }
}
