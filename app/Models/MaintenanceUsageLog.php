<?php

// MaintenanceUsageLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $equipment_id
 * @property string $usage_date
 * @property float $usage_amount
 * @property float|null $meter_reading
 * @property int|null $logged_by
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read MaintenanceEquipmentAsset $equipment
 * @property-read User|null $loggedBy
 */
class MaintenanceUsageLog extends Model
{
    protected $fillable = [
        'equipment_id',
        'usage_date',
        'usage_amount',
        'meter_reading',
        'logged_by',
        'notes',
    ];

    protected $casts = [
        'usage_date' => 'date',
        'usage_amount' => 'decimal:2',
        'meter_reading' => 'decimal:2',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(MaintenanceEquipmentAsset::class, 'equipment_id');
    }

    public function loggedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by');
    }
}
