<?php

namespace Modules\UPF\Services;

use Illuminate\Support\Facades\DB;
use Modules\UPF\Models\UpfPrice;
use Modules\UPF\Models\UpfStockItem;
use Modules\UPF\Models\UpfStockMovement;

class StockService
{
    public function adjustStock(int $upfPriceId, string $location, float $quantity, string $type, ?string $ref = null, ?string $notes = null): void
    {
        DB::transaction(function () use ($upfPriceId, $location, $quantity, $type, $ref, $notes) {
            $price = UpfPrice::findOrFail($upfPriceId);
            $stockItem = UpfStockItem::firstOrCreate(
                ['upf_price_id' => $upfPriceId, 'location' => $location],
                ['type' => $price->type, 'size' => $price->size, 'grade_name' => $price->grade_name, 'uom' => $price->price_unit ?? 'LB'],
            );

            $quantityBefore = (float) $stockItem->quantity_on_hand;
            if ($type === 'RECEIPT') {
                $stockItem->increment('quantity_on_hand', $quantity);
            } elseif ($type === 'ISSUE') {
                $stockItem->decrement('quantity_on_hand', $quantity);
            }

            UpfStockMovement::create([
                'upf_stock_item_id' => $stockItem->id,
                'transaction_type' => $type,
                'transaction_number' => $ref,
                'transaction_date' => now(),
                'quantity' => $quantity,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $stockItem->quantity_on_hand,
                'unit_cost' => $price->unit_price,
                'total_cost' => $quantity * $price->unit_price,
                'from_location' => $location,
                'to_location' => $location,
                'user_id' => auth()->id(),
                'notes' => $notes,
            ]);
        });
    }
}
