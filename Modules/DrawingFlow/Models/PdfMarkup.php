<?php

namespace Modules\DrawingFlow\Models;

use App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PdfMarkup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'submittal_file_id',
        'user_id',
        'page_number',
        'markup_type',
        'markup_data',
    ];

    protected function casts(): array
    {
        return [
            'page_number' => 'integer',
            'markup_data' => 'array',
        ];
    }

    // Relationships

    public function submittalFile(): BelongsTo
    {
        return $this->belongsTo(SubmittalFile::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
