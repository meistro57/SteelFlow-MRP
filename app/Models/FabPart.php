<?php

namespace App\Models;

use App\Enums\PartStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $fab_job_id
 * @property string $mark_number
 * @property string|null $description
 * @property int $quantity
 * @property string|null $material_grade
 * @property float|null $weight_each
 * @property \App\Enums\PartStatus $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * 
 * @property-read \App\Models\FabJob $fabJob
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BomItem> $bomItems
 * @property-read float $total_weight
 */
class FabPart extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'fab_job_id',
        'mark_number',
        'description',
        'quantity',
        'material_grade',
        'weight_each',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => PartStatus::class,
            'quantity' => 'integer',
            'weight_each' => 'decimal:4',
        ];
    }

    public function fabJob(): BelongsTo
    {
        return $this->belongsTo(FabJob::class);
    }

    public function bomItems(): HasMany
    {
        return $this->hasMany(BomItem::class);
    }

    public function getTotalWeightAttribute(): float
    {
        return ($this->weight_each ?? 0) * $this->quantity;
    }
}
