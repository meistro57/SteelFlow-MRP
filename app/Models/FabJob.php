<?php

namespace App\Models;

use App\Enums\JobStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property \Carbon\Carbon|null $due_date
 * @property JobStatus $status
 */
class FabJob extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'job_number',
        'customer_name',
        'description',
        'status',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'status' => JobStatus::class,
            'due_date' => 'date',
        ];
    }

    public function parts(): HasMany
    {
        return $this->hasMany(FabPart::class);
    }

    public function getTotalPartsAttribute(): int
    {
        return $this->parts()->sum('quantity');
    }

    public function getTotalWeightAttribute(): float
    {
        return (float) $this->parts()->sum(\DB::raw('weight_each * quantity'));
    }
}
