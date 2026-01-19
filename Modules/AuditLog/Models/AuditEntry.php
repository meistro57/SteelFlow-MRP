<?php

namespace Modules\AuditLog\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditEntry extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'auditable_id',
        'auditable_type',
        'user_id',
        'action',
        'changes',
        'original',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'changes' => 'array',
        'original' => 'array',
    ];

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
