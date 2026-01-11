<?php

namespace App\Models;

use App\Enums\JobStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $job_number
 * @property string|null $customer_name
 * @property string|null $description
 * @property \App\Enums\JobStatus $status
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FabPart> $parts
 * @property-read int $total_parts
 * @property-read float $total_weight
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
