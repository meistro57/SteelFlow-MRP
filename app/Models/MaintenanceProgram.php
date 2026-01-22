<?php

// MaintenanceProgram.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $equipment_id
 * @property string $title
 * @property int $interval_value
 * @property string $interval_unit
 * @property string|null $checklist
 * @property string|null $required_parts
 * @property string|null $safety_standards
 * @property string|null $procedure_reference
 * @property float|null $estimated_hours
 * @property string $priority
 * @property bool $is_active
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read MaintenanceEquipmentAsset $equipment
 * @property-read User|null $createdBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MaintenanceProgramTask> $programTasks
 */
class MaintenanceProgram extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'equipment_id',
        'title',
        'interval_value',
        'interval_unit',
        'checklist',
        'required_parts',
        'safety_standards',
        'procedure_reference',
        'estimated_hours',
        'priority',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'estimated_hours' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(MaintenanceEquipmentAsset::class, 'equipment_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function programTasks(): HasMany
    {
        return $this->hasMany(MaintenanceProgramTask::class, 'program_id')->orderBy('sequence');
    }
}
