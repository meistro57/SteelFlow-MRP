<?php

// app/Models/ProductionItem.php

declare(strict_types=1);

namespace App\Models;

use App\States\ProductionItem\ProductionItemState;
use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStates\HasStates;

/**
 * @property int $id
 * @property string $name
 * @property string $part_number
 * @property \App\States\ProductionItem\ProductionItemState $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $display_status
 */
class ProductionItem extends Model
{
    use HasStates;

    protected $fillable = [
        'name',
        'part_number',
        'status',
    ];

    protected $casts = [
        'status' => ProductionItemState::class,
    ];

    public function getDisplayStatusAttribute(): string
    {
        return $this->status->label();
    }

    public function nextStatus(): ?string
    {
        return $this->status->next();
    }
}
