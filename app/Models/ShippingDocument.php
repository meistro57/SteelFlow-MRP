<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingDocument extends Model
{
    protected $fillable = [
        'load_id',
        'document_type',
        'file_path',
        'generated_at',
        'sent_at',
    ];

    public function load(): BelongsTo
    {
        return $this->belongsTo(Load::class);
    }
}
