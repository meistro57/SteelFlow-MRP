<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $department_id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property int $sort_order
 * @property bool $is_active
 * @property string|null $badge_barcode
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $active_count
 * @property int|null $total_steps
 * @property int|null $completed_steps
 * @property float|null $total_hours
 * @property int|null $parts_completed
 * @property-read string $badge_code
 * @property-read \App\Models\Department $department
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PartWorkArea> $routingSteps
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TimeEntry> $timeEntries
 */
class WorkArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'code',
        'name',
        'description',
        'sort_order',
        'is_active',
        'badge_barcode',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function routingSteps(): HasMany
    {
        return $this->hasMany(PartWorkArea::class);
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function getBadgeCodeAttribute(): string
    {
        return $this->code;
    }
}
