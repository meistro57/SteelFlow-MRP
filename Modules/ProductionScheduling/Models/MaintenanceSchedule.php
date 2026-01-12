<?php

namespace Modules\ProductionScheduling\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceSchedule extends Model
{
    protected $fillable = ['machine_id', 'scheduled_date', 'description', 'status'];
}
