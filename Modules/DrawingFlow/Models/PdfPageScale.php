<?php

namespace Modules\DrawingFlow\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PdfPageScale extends Model
{
    use HasFactory;

    protected $fillable = [
        'submittal_file_id',
        'page_number',
        'calibration_distance',
        'real_length',
        'unit',
    ];

    protected function casts(): array
    {
        return [
            'page_number' => 'integer',
            'calibration_distance' => 'float',
            'real_length' => 'float',
        ];
    }

    public function submittalFile(): BelongsTo
    {
        return $this->belongsTo(SubmittalFile::class, 'submittal_file_id');
    }
}
