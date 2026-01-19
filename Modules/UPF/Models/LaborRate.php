<?php

namespace Modules\UPF\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaborRate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'material_type_id',
        'type',
        'operation_code',
        'operation_name',
        'description',
        'pkey',
        'rate_per_hour',
        'rate_per_unit',
        'setup_time_minutes',
        'run_time_per_unit',
        'efficiency_factor',
        'crew_size',
        'equipment_required',
        'equipment_rate_per_hour',
        'is_active',
        'display_order',
        'metadata',
    ];

    protected $casts = [
        'rate_per_hour' => 'decimal:2',
        'rate_per_unit' => 'decimal:2',
        'setup_time_minutes' => 'decimal:2',
        'run_time_per_unit' => 'decimal:2',
        'efficiency_factor' => 'decimal:4',
        'equipment_rate_per_hour' => 'decimal:2',
        'is_active' => 'boolean',
        'metadata' => 'json',
    ];

    public function materialType(): BelongsTo
    {
        return $this->belongsTo(MaterialType::class);
    }
}
