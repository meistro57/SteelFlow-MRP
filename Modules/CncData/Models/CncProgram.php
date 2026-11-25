<?php

declare(strict_types=1);

namespace Modules\CncData\Models;

use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CncData\Enums\CncProgramStatus;
use Modules\Core\Traits\HasAuditFields;
use Modules\ProductionScheduling\Models\Machine;

/**
 * A plasma CNC program file plus the production data read from it.
 *
 * @property int $id
 * @property int|null $project_id
 * @property int|null $machine_id
 * @property string $program_name
 * @property string $file_path
 * @property string $file_hash
 * @property CncProgramStatus $status
 * @property float|null $thickness
 * @property int|null $amps
 * @property float $est_minutes
 * @property float $arc_minutes
 * @property float|null $calibration_factor
 * @property float|null $est_minutes_calibrated
 * @property bool $estimate_validated
 * @property array $analysis
 */
class CncProgram extends Model
{
    use HasAuditFields, SoftDeletes;

    protected $fillable = [
        'project_id', 'machine_id', 'program_name', 'file_path', 'file_hash', 'file_size', 'source', 'status',
        'units', 'material_name', 'thickness', 'sheet_x', 'sheet_y', 'process_label', 'amps',
        'cut_length', 'mark_length', 'travel_length', 'rapid_length', 'cut_pierces', 'true_hole_pierces', 'mark_starts',
        'rapid_rate', 'est_minutes', 'arc_minutes', 'calibration_factor', 'est_minutes_calibrated', 'estimate_validated',
        'error_count', 'warning_count', 'chart_feed_ok', 'chart_kerf_ok',
        'schema_version', 'analysis', 'analyzed_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => CncProgramStatus::class,
            'analysis' => 'array',
            'analyzed_at' => 'datetime',
            'estimate_validated' => 'boolean',
            'chart_feed_ok' => 'boolean',
            'chart_kerf_ok' => 'boolean',
            'thickness' => 'float',
            'sheet_x' => 'float',
            'sheet_y' => 'float',
            'cut_length' => 'float',
            'mark_length' => 'float',
            'travel_length' => 'float',
            'rapid_length' => 'float',
            'rapid_rate' => 'float',
            'est_minutes' => 'float',
            'arc_minutes' => 'float',
            'calibration_factor' => 'float',
            'est_minutes_calibrated' => 'float',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function parts(): HasMany
    {
        return $this->hasMany(CncProgramPart::class);
    }

    public function runs(): HasMany
    {
        return $this->hasMany(CncRun::class);
    }

    /** Best available machine time: calibrated once validated, otherwise the engine estimate. */
    public function plannedMinutes(): float
    {
        return $this->estimate_validated && $this->est_minutes_calibrated !== null
            ? $this->est_minutes_calibrated
            : $this->est_minutes;
    }

    /** @return list<array{line:int,code:string,severity:string,message:string}> */
    public function warnings(): array
    {
        return $this->analysis['warnings'] ?? [];
    }
}
