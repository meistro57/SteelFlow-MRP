<?php

namespace App\Models;

use App\Enums\MaterialType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property MaterialType $material_type
 */
class RawMaterial extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'material_type',
        'size_description',
        'grade',
        'length_stock',
        'quantity_on_hand',
        'location',
    ];

    protected function casts(): array
    {
        return [
            'material_type' => MaterialType::class,
            'length_stock' => 'decimal:4',
            'quantity_on_hand' => 'integer',
        ];
    }

    public function bomItems(): HasMany
    {
        return $this->hasMany(BomItem::class);
    }

    public function isLowStock(): bool
    {
        return $this->quantity_on_hand < 5;
    }

    public function getDisplayNameAttribute(): string
    {
        return "{$this->material_type->getLabel()} - {$this->size_description} ({$this->grade})";
    }
}
