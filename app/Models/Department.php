<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int $sort_order
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WorkArea> $workAreas
 */
class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'sort_order',
        'is_active',
    ];

    public function workAreas(): HasMany
    {
        return $this->hasMany(WorkArea::class);
    }
}
