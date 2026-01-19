<?php

namespace Modules\Documents\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $document_id
 * @property string $file_path
 * @property int $version
 * @property string|null $notes
 * @property array|null $metadata
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\Documents\Models\Document $document
 * @property-read \App\Models\User|null $creator
 */
class DocumentVersion extends Model
{
    protected $fillable = [
        'document_id',
        'file_path',
        'version',
        'notes',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'metadata' => 'array',
        'version' => 'integer',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
