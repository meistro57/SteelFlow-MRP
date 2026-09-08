<?php

namespace Modules\DrawingFlow\Models;

use App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubmittalFile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'submittal_id',
        'file_type',
        'filename',
        'original_filename',
        'file_path',
        'file_size',
        'mime_type',
        'version',
        'is_current',
        'uploaded_by_user_id',
        'uploaded_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'version' => 'integer',
            'is_current' => 'boolean',
            'uploaded_at' => 'datetime',
        ];
    }

    // Relationships

    public function submittal(): BelongsTo
    {
        return $this->belongsTo(DrawingSubmittal::class, 'submittal_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function markups(): HasMany
    {
        return $this->hasMany(PdfMarkup::class, 'submittal_file_id');
    }

    public function pageScales(): HasMany
    {
        return $this->hasMany(PdfPageScale::class, 'submittal_file_id');
    }

    // Scopes

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    // Helpers

    public function isDrawing(): bool
    {
        return $this->file_type === 'drawing';
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2).' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2).' KB';
        }

        return $bytes.' bytes';
    }
}
