<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationLineItem extends Model
{
    protected $fillable = [
        'application_id',
        'sov_id',
        'percent_complete',
        'total_earned',
        'previous_earned',
        'current_earned',
    ];

    protected $casts = [
        'percent_complete' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'previous_earned' => 'decimal:2',
        'current_earned' => 'decimal:2',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(ApplicationForPayment::class, 'application_id');
    }

    public function sov(): BelongsTo
    {
        return $this->belongsTo(ScheduleOfValue::class, 'sov_id');
    }
}
