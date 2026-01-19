<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $nesting_id
 * @property int $nesting_bar_id
 * @property int $part_instance_id
 * @property int $position_on_bar
 * @property float $cut_length
 * @property float $start_position
 * @property bool $is_cut
 * @property int|null $cut_by
 * @property \Illuminate\Support\Carbon|null $cut_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Nesting $nesting
 * @property-read \App\Models\NestingBar $bar
 * @property-read \App\Models\PartInstance $partInstance
 * @property-read \App\Models\User|null $cutter
 */
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
