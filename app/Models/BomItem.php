<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $fab_part_id
 * @property int $raw_material_id
 * @property int $quantity_required
 * @property float $cut_length
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read \App\Models\FabPart $fabPart
 * @property-read \App\Models\RawMaterial $rawMaterial
 */
class BomItem extends Model
{
    protected $fillable = [
        'fab_part_id',
        'raw_material_id',
        'quantity_required',
        'cut_length',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_required' => 'integer',
            'cut_length' => 'decimal:4',
        ];
    }

    public function fabPart(): BelongsTo
    {
        return $this->belongsTo(FabPart::class);
    }

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class);
    }
}
