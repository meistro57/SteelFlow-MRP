<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
 * @property-read \App\Models\Department $department
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PartWorkArea> $routingSteps
 */
class WorkArea extends Model
{
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
}
