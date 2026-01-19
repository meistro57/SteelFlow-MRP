<?php

namespace Modules\Inventory\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $count_number
 * @property string $status
 * @property string|null $stock_area
 * @property string|null $type_filter
 * @property string|null $grade_filter
 * @property \Illuminate\Support\Carbon|null $scheduled_date
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property int|null $assigned_to
 * @property int|null $created_by
 * @property int|null $completed_by
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Inventory\Models\CycleCountLine> $lines
 * @property-read \App\Models\User|null $assignedUser
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\User|null $completedByUser
 */
class CycleCount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'count_number',
        'status',
        'stock_area',
        'type_filter',
        'grade_filter',
        'scheduled_date',
        'started_at',
        'completed_at',
        'assigned_to',
        'created_by',
        'completed_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(CycleCountLine::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Get the count of items with variances.
     */
    public function getVarianceCountAttribute(): int
    {
        return $this->lines()->whereColumn('counted_quantity', '!=', 'expected_quantity')->count();
    }

    /**
     * Get the total value of variances.
     */
    public function getTotalVarianceAttribute(): int
    {
        return $this->lines->sum(function ($line) {
            return $line->counted_quantity - $line->expected_quantity;
        });
    }
}
