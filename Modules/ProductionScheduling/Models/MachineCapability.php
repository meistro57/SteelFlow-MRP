<?php

namespace Modules\ProductionScheduling\Models;

use Illuminate\Database\Eloquent\Model;

class MachineCapability extends Model
{
    protected $fillable = ['machine_id', 'capability_name', 'efficiency_factor'];
}
