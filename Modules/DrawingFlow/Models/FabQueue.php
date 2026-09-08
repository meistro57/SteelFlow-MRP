<?php

namespace Modules\DrawingFlow\Models;

use App\Models\Project;
use App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FabQueue extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'fab_queue';

    protected $fillable = [
        'submittal_id',
        'project_id',
        'queue_number',
        'priority',
        'material_requirements',
        'cnc_files_attached',
        'assigned_to_user_id',
        'assigned_at',
        'status',
        'started_at',
        'completed_at',
        'notes',
        'shop_notes',
        'date_released',
        'source_created_at',
        'source_modified_at',
        'source_modified_by',
        'source_status',
        'attachments_count',
        'model_link',
        'import_source',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
            'cnc_files_attached' => 'boolean',
            'assigned_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'date_released' => 'date',
            'source_created_at' => 'datetime',
            'source_modified_at' => 'datetime',
            'attachments_count' => 'integer',
        ];
    }

    // Relationships

    public function submittal(): BelongsTo
    {
        return $this->belongsTo(DrawingSubmittal::class, 'submittal_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    // Scopes

    public function scopeQueued($query)
    {
        return $query->where('status', 'queued');
    }

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'asc');
    }

    // Helpers

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'queued' => 'Queued',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'on_hold' => 'On Hold',
            default => ucfirst($this->status),
        };
    }
}
