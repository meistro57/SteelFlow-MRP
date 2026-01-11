<?php

namespace Modules\ShopTicket\Models;

use App\Models\Employee;
use App\Models\WorkArea;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $shop_ticket_id
 * @property int $work_area_id
 * @property int $sequence
 * @property string $status
 * @property float $estimated_hours
 * @property float $actual_hours
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property int|null $completed_by
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read ShopTicket $shopTicket
 * @property-read WorkArea $workArea
 * @property-read Employee|null $completer
 */
class ShopTicketStep extends Model
{
    protected $fillable = [
        'shop_ticket_id',
        'work_area_id',
        'sequence',
        'status',
        'estimated_hours',
        'actual_hours',
        'started_at',
        'completed_at',
        'completed_by',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function shopTicket(): BelongsTo
    {
        return $this->belongsTo(ShopTicket::class);
    }

    public function workArea(): BelongsTo
    {
        return $this->belongsTo(WorkArea::class);
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'completed_by');
    }
}
