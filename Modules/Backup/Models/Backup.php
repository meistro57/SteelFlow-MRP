<?php

namespace Modules\Backup\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Backup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'status',
        'path',
        'cloud_path',
        'size',
        'started_at',
        'completed_at',
        'error_message',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'size' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if backup is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if backup failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if backup is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Get human-readable file size.
     */
    public function getFormattedSizeAttribute(): string
    {
        if (! $this->size) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = $this->size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2).' '.$units[$unit];
    }

    /**
     * Get duration of backup operation.
     */
    public function getDurationAttribute(): ?int
    {
        if (! $this->started_at || ! $this->completed_at) {
            return null;
        }

        return $this->started_at->diffInSeconds($this->completed_at);
    }
}
