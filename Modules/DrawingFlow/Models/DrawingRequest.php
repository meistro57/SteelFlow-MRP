<?php

namespace Modules\DrawingFlow\Models;

use App\Models\Customer;
use App\Models\Project;
use App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DrawingRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'request_number',
        'project_id',
        'customer_id',
        'requested_by_user_id',
        'assigned_to_user_id',
        'title',
        'description',
        'priority',
        'drawing_type',
        'job_number',
        'customer_address',
        'required_date',
        'requested_date',
        'status',
        'notes',
        'source_modified_at',
        'source_modified_by',
        'source_created_by',
        'source_assigned_to',
        'source_discipline',
        'source_status',
        'attachments_count',
        'pointcloud_link',
        'job_link',
        'source_image',
        'import_source',
    ];

    protected function casts(): array
    {
        return [
            'required_date' => 'date',
            'requested_date' => 'date',
            'source_modified_at' => 'datetime',
            'attachments_count' => 'integer',
        ];
    }

    // Relationships

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function submittals(): HasMany
    {
        return $this->hasMany(DrawingSubmittal::class);
    }

    // Scopes

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    // Helpers

    public function isAssigned(): bool
    {
        return $this->assigned_to_user_id !== null;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'in_progress' => 'In Progress',
            'ready_to_submit' => 'Ready to Submit',
            'submitted' => 'Submitted',
            'approved' => 'Approved',
            'on_hold' => 'On Hold',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }
}
