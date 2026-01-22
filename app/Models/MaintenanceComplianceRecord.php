<?php

// MaintenanceComplianceRecord.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $equipment_id
 * @property string $standard
 * @property string $status
 * @property string|null $last_audit_date
 * @property string|null $next_audit_due
 * @property string|null $evidence_location
 * @property string|null $notes
 * @property int|null $audited_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read MaintenanceEquipmentAsset $equipment
 * @property-read User|null $auditedBy
 */
class MaintenanceComplianceRecord extends Model
{
    protected $fillable = [
        'equipment_id',
        'standard',
        'status',
        'last_audit_date',
        'next_audit_due',
        'evidence_location',
        'notes',
        'audited_by',
    ];

    protected $casts = [
        'last_audit_date' => 'date',
        'next_audit_due' => 'date',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(MaintenanceEquipmentAsset::class, 'equipment_id');
    }

    public function auditedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'audited_by');
    }
}
