<?php

namespace Modules\ProductionScheduling\Models;

use App\Models\Assembly;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrder extends Model
{
    protected $fillable = [
        'assembly_id',
        'production_batch_id',
        'operation_sequence',
        'operation_type',
        'machine_id',
        'assigned_user_id',
        'estimated_hours',
        'actual_hours',
        'scheduled_start',
        'scheduled_end',
        'actual_start',
        'actual_end',
        'status',
        'priority',
    ];

    protected $casts = [
        'scheduled_start' => 'datetime',
        'scheduled_end' => 'datetime',
        'actual_start' => 'datetime',
        'actual_end' => 'datetime',
    ];

    public function assembly(): BelongsTo
    {
        return $this->belongsTo(Assembly::class);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }
}
