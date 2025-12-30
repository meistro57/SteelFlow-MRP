<?php

namespace App\Services\Nesting;

use App\Models\Nesting;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class NestingService
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Approve a nesting: Marks stock as ASSIGNED.
     */
    public function approve(Nesting $nesting): void
    {
        DB::transaction(function () use ($nesting) {
            $nesting->bars->each(function ($bar) {
                if ($bar->stockItem) {
                    $bar->stockItem->update(['status' => 'assigned']);
                    $this->inventoryService->recordMovement($bar->stockItem, 'assign', 1, [
                        'to_status' => 'assigned',
                        'reference_type' => 'nesting',
                        'reference_id' => $bar->nesting_id,
                    ]);
                }
            });

            $nesting->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
        });
    }

    /**
     * Confirm a nesting: Marks stock as USED and creates remnants.
     */
    public function confirm(Nesting $nesting): void
    {
        DB::transaction(function () use ($nesting) {
            $nesting->bars->each(function ($bar) {
                if ($bar->stockItem) {
                    $bar->stockItem->update(['status' => 'used']);
                    $this->inventoryService->recordMovement($bar->stockItem, 'use', 1, [
                        'to_status' => 'used',
                        'reference_type' => 'nesting',
                        'reference_id' => $bar->nesting_id,
                    ]);

                    // Create remnant if applicable
                    if ($bar->remnant_length > 0) {
                        $this->createRemnant($bar);
                    }
                }
            });

            $nesting->update([
                'status' => 'confirmed',
                'confirmed_by' => Auth::id(),
                'confirmed_at' => now(),
            ]);
        });
    }

    protected function createRemnant($bar): void
    {
        $parent = $bar->stockItem;
        
        $remnant = StockItem::create([
            'stock_id' => 'REM-' . strtoupper(uniqid()),
            'material_id' => $parent->material_id,
            'type' => $parent->type,
            'size' => $parent->size,
            'grade' => $parent->grade,
            'length' => $bar->remnant_length,
            'quantity' => 1,
            'status' => 'free',
            'reserved_project_id' => $parent->reserved_project_id,
            'stock_area' => $parent->stock_area,
            'is_remnant' => true,
            'parent_stock_id' => $parent->id,
            'receive_date' => now(),
        ]);

        $this->inventoryService->recordMovement($remnant, 'receive', 1, [
            'notes' => 'Created as remnant from nesting ' . $bar->nesting->nesting_number,
        ]);
    }
}
