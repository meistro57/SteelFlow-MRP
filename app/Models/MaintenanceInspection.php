<?php

// MaintenanceInspection.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $equipment_id
 * @property int|null $inspector_id
 * @property string $inspection_date
 * @property string $condition_rating
 * @property string $compliance_status
 * @property string|null $findings
 * @property string|null $next_due_date
 * @property bool $follow_up_required
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read MaintenanceEquipmentAsset $equipment
 * @property-read User|null $inspector
 */
class MaintenanceInspection extends Model
{
    protected $fillable = [
        'equipment_id',
        'inspector_id',
        'inspection_date',
        'condition_rating',
        'compliance_status',
        'findings',
        'next_due_date',
        'follow_up_required',
    ];

    protected $casts = [
        'inspection_date' => 'date',
        'next_due_date' => 'date',
        'follow_up_required' => 'boolean',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(MaintenanceEquipmentAsset::class, 'equipment_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }
}
