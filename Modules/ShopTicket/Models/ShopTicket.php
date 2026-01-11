<?php

namespace Modules\ShopTicket\Models;

use App\Models\AssemblyInstance;
use App\Models\Employee;
use App\Models\PartInstance;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $ticket_number
 * @property int $project_id
 * @property int|null $assembly_instance_id
 * @property int|null $part_instance_id
 * @property string $status
 * @property int $priority
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property string|null $notes
 * @property int $created_by
 * @property int|null $assigned_to_employee_id
 * @property \Illuminate\Support\Carbon|null $released_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * 
 * @property-read Project $project
 * @property-read AssemblyInstance|null $assemblyInstance
 * @property-read PartInstance|null $partInstance
 * @property-read User $creator
 * @property-read Employee|null $assignedEmployee
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ShopTicketStep> $steps
 */
class ShopTicket extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ticket_number',
        'project_id',
        'assembly_instance_id',
        'part_instance_id',
        'status',
        'priority',
        'due_date',
        'notes',
        'created_by',
        'assigned_to_employee_id',
        'released_at',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'released_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assemblyInstance(): BelongsTo
    {
        return $this->belongsTo(AssemblyInstance::class);
    }

    public function partInstance(): BelongsTo
    {
        return $this->belongsTo(PartInstance::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_to_employee_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ShopTicketStep::class)->orderBy('sequence');
    }
}
