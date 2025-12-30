<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NestingPart extends Model
{
    protected $fillable = [
        'nesting_id',
        'nesting_bar_id',
        'part_instance_id',
        'position_on_bar',
        'cut_length',
        'start_position',
        'is_cut',
        'cut_by',
        'cut_at',
    ];

    public function nesting(): BelongsTo
    {
        return $this->belongsTo(Nesting::class);
    }

    public function bar(): BelongsTo
    {
        return $this->belongsTo(NestingBar::class, 'nesting_bar_id');
    }

    public function partInstance(): BelongsTo
    {
        return $this->belongsTo(PartInstance::class);
    }

    public function cutter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cut_by');
    }
}
