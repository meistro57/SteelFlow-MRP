<?php

namespace Modules\ProductionScheduling\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Machine extends Model
{
    protected $fillable = [
        'name',
        'machine_type',
        'location_id',
        'hourly_capacity',
        'status',
        'notes',
    ];

    public function capabilities(): HasMany
    {
        return $this->hasMany(MachineCapability::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(MachineSchedule::class);
    }

    public function maintenance(): HasMany
    {
        return $this->hasMany(MaintenanceSchedule::class);
    }
}
