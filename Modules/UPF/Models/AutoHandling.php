<?php

namespace Modules\UPF\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AutoHandling extends Model
{
    use SoftDeletes;

    protected $table = 'auto_handling';

    protected $fillable = [
        'material_type_id',
        'type',
        'handling_code',
        'handling_name',
        'description',
        'pkey',
        'cost_per_ton',
        'cost_per_piece',
        'cost_per_foot',
        'minimum_charge',
        'min_weight',
        'max_weight',
        'min_length',
        'max_length',
        'is_active',
        'auto_apply',
        'display_order',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'auto_apply' => 'boolean',
        'cost_per_ton' => 'decimal:2',
        'cost_per_piece' => 'decimal:2',
        'cost_per_foot' => 'decimal:2',
        'minimum_charge' => 'decimal:2',
        'metadata' => 'json',
    ];

    public function materialType(): BelongsTo
    {
        return $this->belongsTo(MaterialType::class);
    }
}
