<?php

namespace Modules\Backup\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string $frequency
 * @property string|null $time
 * @property bool $enabled
 * @property int $retention_days
 * @property bool $cloud_sync
 * @property \Illuminate\Support\Carbon|null $last_run_at
 * @property \Illuminate\Support\Carbon|null $next_run_at
 * @property int|null $created_by
 * @property array|null $options
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection|\Modules\Backup\Models\Backup[] $backups
 * @property-read int|null $backups_count
 */
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
