<?php

declare(strict_types=1);

namespace Modules\CncData\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Shop cost rates for one plasma process. Entered by the shop from its own
 * consumable spend — the XPR manuals give no fixed consumable life to derive
 * these from, so nothing here is defaulted.
 */
class CncProcessRate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'machine_id', 'amps', 'material', 'thickness', 'cost_per_pierce', 'cost_per_arc_minute',
        'machine_rate_per_hour', 'effective_from', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'thickness' => 'float',
            'cost_per_pierce' => 'float',
            'cost_per_arc_minute' => 'float',
            'machine_rate_per_hour' => 'float',
            'effective_from' => 'date',
        ];
    }
}
