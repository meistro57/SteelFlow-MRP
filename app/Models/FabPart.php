<?php

namespace App\Models;

use App\Enums\PartStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
