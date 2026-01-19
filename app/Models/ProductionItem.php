<?php

// app/Models/ProductionItem.php

declare(strict_types=1);

namespace App\Models;

use App\States\ProductionItem\ProductionItemState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\ModelStates\HasStates;

/**
 * @property int $id
 * @property string $name
 * @property string $part_number
 * @property int|null $part_instance_id
 * @property \App\States\ProductionItem\ProductionItemState $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $display_status
 * @property-read \App\Models\PartInstance|null $partInstance
 */
class ProductionItem extends Model
{
    use HasStates;

    protected $fillable = [
        'name',
        'part_number',
        'part_instance_id',
        'status',
    ];

    protected $casts = [
        'status' => ProductionItemState::class,
    ];

    public function partInstance(): BelongsTo
    {
        return $this->belongsTo(PartInstance::class);
    }

    public function getDisplayStatusAttribute(): string
    {
        return $this->status->label();
    }

    public function nextStatus(): ?string
    {
        return $this->status->next();
    }
}
