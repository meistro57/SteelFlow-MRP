<?php

namespace Modules\Backup\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BackupSchedule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'frequency',
        'time',
        'enabled',
        'retention_days',
        'cloud_sync',
        'last_run_at',
        'next_run_at',
        'created_by',
        'options',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'cloud_sync' => 'boolean',
        'retention_days' => 'integer',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
        'options' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function backups(): HasMany
    {
        return $this->hasMany(Backup::class, 'schedule_id');
    }

    /**
     * Check if schedule is active.
     */
    public function isActive(): bool
    {
        return $this->enabled;
    }

    /**
     * Check if schedule is due to run.
     */
    public function isDue(): bool
    {
        if (! $this->enabled) {
            return false;
        }

        if (! $this->next_run_at) {
            return true;
        }

        return $this->next_run_at->isPast();
    }

    /**
     * Calculate next run time based on frequency.
     */
    public function calculateNextRun(): void
    {
        $baseTime = $this->last_run_at ?? now();

        $nextRun = match ($this->frequency) {
            'hourly' => $baseTime->addHour(),
            'daily' => $baseTime->addDay()->setTimeFromTimeString($this->time ?? '02:00'),
            'weekly' => $baseTime->addWeek()->setTimeFromTimeString($this->time ?? '02:00'),
            'monthly' => $baseTime->addMonth()->setTimeFromTimeString($this->time ?? '02:00'),
            default => null,
        };

        if ($nextRun) {
            $this->next_run_at = $nextRun;
            $this->save();
        }
    }
}
