<?php

declare(strict_types=1);

namespace Modules\CncData\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Traits\HasAuditFields;
use Modules\ProductionScheduling\Models\Machine;

/** One actual execution of a program on the table — the ground truth estimates are checked against. */
class CncRun extends Model
{
    use HasAuditFields, SoftDeletes;

    protected $fillable = [
        'cnc_program_id', 'machine_id', 'operator_id', 'started_at', 'finished_at', 'actual_minutes',
        'arc_on_minutes', 'pierce_count', 'result', 'source', 'est_minutes_at_run', 'variance_pct', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'actual_minutes' => 'float',
            'arc_on_minutes' => 'float',
            'est_minutes_at_run' => 'float',
            'variance_pct' => 'float',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(CncProgram::class, 'cnc_program_id');
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }
}
