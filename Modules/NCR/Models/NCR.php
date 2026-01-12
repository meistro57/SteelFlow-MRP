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

/**
 * @property int $id
 * @property string $number
 * @property int|null $production_item_id
 * @property int|null $part_instance_id
 * @property int|null $stock_item_id
 * @property \Modules\NCR\States\NCRState $status
 * @property string|null $disposition
 * @property string $failure_reason
 * @property string|null $remediation_notes
 * @property float|null $scrap_cost
 * @property string|null $rework_operation
 * @property int $reported_by
 * @property int|null $dispositioned_by
 * @property \Illuminate\Support\Carbon|null $dispositioned_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\ProductionItem|null $productionItem
 * @property-read \App\Models\PartInstance|null $partInstance
 * @property-read \Modules\Inventory\Models\StockItem|null $stockItem
 * @property-read \App\Models\User|null $reporter
 * @property-read \App\Models\User|null $dispositioner
 */
class NCR extends Model
{
    use HasAuditFields, HasStates, SoftDeletes;

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
