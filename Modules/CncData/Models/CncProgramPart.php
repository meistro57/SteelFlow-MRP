<?php

declare(strict_types=1);

namespace Modules\CncData\Models;

use App\Models\Assembly;
use App\Models\Part;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Links a CNC program to the piece marks (and their parts/assemblies) cut on it. */
class CncProgramPart extends Model
{
    use SoftDeletes;

    protected $fillable = ['cnc_program_id', 'part_id', 'assembly_id', 'piece_mark', 'quantity', 'source'];

    public function program(): BelongsTo
    {
        return $this->belongsTo(CncProgram::class, 'cnc_program_id');
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public function assembly(): BelongsTo
    {
        return $this->belongsTo(Assembly::class);
    }
}
