<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
