<?php

namespace App\Models;

use App\Enums\MaterialType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property \App\Enums\MaterialType $material_type
 * @property string $size_description
 * @property string|null $grade
 * @property float|null $length_stock
 * @property int $quantity_on_hand
 * @property string|null $location
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BomItem> $bomItems
 * @property-read string $display_name
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
