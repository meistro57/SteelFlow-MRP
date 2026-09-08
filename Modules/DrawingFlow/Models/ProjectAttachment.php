<?php

namespace Modules\DrawingFlow\Models;

use App\Models\Project;
use App\Models\User;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'filename',
        'original_filename',
        'file_path',
        'document_key',
        'category',
        'version_number',
        'is_latest',
        'file_size',
        'mime_type',
        'uploaded_by_user_id',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'version_number' => 'integer',
            'is_latest' => 'boolean',
            'file_size' => 'integer',
            'uploaded_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function scopeLatestVersions(Builder $query): Builder
    {
        return $query->where('is_latest', true);
    }

    public function scopeForDocumentKey(Builder $query, string $documentKey): Builder
    {
        return $query->where('document_key', $documentKey);
    }
}
