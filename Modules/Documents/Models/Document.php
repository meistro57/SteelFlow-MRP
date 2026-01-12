<?php

namespace Modules\Documents\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $file_path
 * @property string|null $file_type
 * @property string|null $mime_type
 * @property int|null $file_size
 * @property int $version
 * @property array|null $metadata
 * @property int|null $documentable_id
 * @property string|null $documentable_type
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $documentable
 * @property-read \Illuminate\Database\Eloquent\Collection|\Modules\Documents\Models\DocumentVersion[] $versions
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\User|null $updater
 */
class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'file_path',
        'file_type',
        'mime_type',
        'file_size',
        'version',
        'metadata',
        'documentable_id',
        'documentable_type',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'metadata' => 'array',
        'file_size' => 'integer',
        'version' => 'integer',
    ];

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getFormattedSizeAttribute(): string
    {
        if (! $this->file_size) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($this->file_size, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2).' '.$units[$pow];
    }
}
