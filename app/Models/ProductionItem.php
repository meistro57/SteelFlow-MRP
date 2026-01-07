<?php // app/Models/ProductionItem.php

declare(strict_types=1);

namespace App\Models;

use App\States\ProductionItem\ProductionItemState;
use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStates\HasStates;

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

    public string $displayStatus {
        get => $this->status->label();
    }

    public function nextStatus(): ?string
    {
        return $this->status->next();
    }
}
