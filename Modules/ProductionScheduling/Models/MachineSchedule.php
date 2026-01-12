<?php

namespace Modules\ProductionScheduling\Models;

use Illuminate\Database\Eloquent\Model;

class MachineSchedule extends Model
{
    protected $fillable = ['machine_id', 'start_time', 'end_time', 'work_order_id'];
}
