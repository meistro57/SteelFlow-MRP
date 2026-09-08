<?php

namespace Modules\DrawingFlow\Models;

use App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmittalNote extends Model
{
    /** @use HasFactory<\Database\Factories\SubmittalNoteFactory> */
    use HasFactory;

    protected $fillable = [
        'submittal_id',
        'user_id',
        'message',
    ];

    public function submittal(): BelongsTo
    {
        return $this->belongsTo(DrawingSubmittal::class, 'submittal_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
