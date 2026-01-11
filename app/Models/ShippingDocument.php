<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $load_id
 * @property string $document_type
 * @property string $file_path
 * @property \Illuminate\Support\Carbon|null $generated_at
 * @property \Illuminate\Support\Carbon|null $sent_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read \App\Models\Load $truckLoad
 */
class ShippingDocument extends Model
{
    protected $fillable = [
        'load_id',
        'document_type',
        'file_path',
        'generated_at',
        'sent_at',
    ];

    public function truckLoad(): BelongsTo
    {
        return $this->belongsTo(Load::class);
    }
}
