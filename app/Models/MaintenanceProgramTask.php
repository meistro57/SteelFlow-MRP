<?php

// MaintenanceProgramTask.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $program_id
 * @property int $sequence
 * @property string $description
 * @property bool $is_mandatory
 * @property int|null $expected_minutes
 * @property string|null $reference_notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read MaintenanceProgram $program
 */
class MaintenanceProgramTask extends Model
{
    protected $fillable = [
        'program_id',
        'sequence',
        'description',
        'is_mandatory',
        'expected_minutes',
        'reference_notes',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(MaintenanceProgram::class, 'program_id');
    }
}
