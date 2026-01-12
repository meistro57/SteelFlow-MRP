<?php

namespace Modules\NCR\Models;

use App\Models\PartInstance;
use App\Models\ProductionItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Traits\HasAuditFields;
use Modules\Inventory\Models\StockItem;
use Modules\NCR\States\NCRState;
use Spatie\ModelStates\HasStates;

class NCR extends Model
{
    use SoftDeletes, HasStates, HasAuditFields;

    protected $table = 'ncrs';

    protected $fillable = [
        'number',
        'production_item_id',
        'part_instance_id',
        'stock_item_id',
        'status',
        'disposition',
        'failure_reason',
        'remediation_notes',
        'scrap_cost',
        'rework_operation',
        'reported_by',
        'dispositioned_by',
        'dispositioned_at',
    ];

    protected $casts = [
        'status' => NCRState::class,
        'dispositioned_at' => 'datetime',
        'scrap_cost' => 'decimal:2',
    ];

    public function productionItem(): BelongsTo
    {
        return $this->belongsTo(ProductionItem::class);
    }

    public function partInstance(): BelongsTo
    {
        return $this->belongsTo(PartInstance::class);
    }

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function dispositioner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispositioned_by');
    }
}
