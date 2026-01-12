<?php

namespace App\Services\Nesting;

use App\Models\Nesting;
use App\Models\NestingBar;
use App\Models\NestingPart;
use App\Models\PartInstance;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\Models\StockItem;
use Modules\Inventory\Services\InventoryService;

class NestingService
{
    public function __construct(
        protected InventoryService $inventoryService,
    ) {}

    /**
     * Create a new nesting using a simple first-fit decreasing (FFD) algorithm.
     */
    public function createNesting(array $data): Nesting
    {
        $project = Project::findOrFail($data['project_id']);
        $kerf = $data['kerf_allowance'] ?? 0.125;

        // 1. Get all part instances for this project that aren't nested
        $partInstances = PartInstance::where('project_id', $project->id)
            ->whereNull('nesting_id')
            ->with(['part'])
            ->get()
            ->filter(fn ($pi) => $pi->part && $pi->part->length > 0);

        if ($partInstances->isEmpty()) {
            throw new \Exception("No parts found for nesting in project {$project->job_number}.");
        }

        return DB::transaction(function () use ($project, $partInstances, $kerf) {
            $nesting = Nesting::create([
                'nesting_number' => 'NEST-'.strtoupper(uniqid()),
                'type' => 'linear',
                'project_id' => $project->id,
                'status' => 'draft',
                'kerf_allowance' => $kerf,
            ]);

            // Group parts by material (type, size, grade)
            $groups = $partInstances->groupBy(function ($pi) {
                return $pi->part->type.'|'.$pi->part->size_imperial.'|'.$pi->part->grade;
            });

            $totalBars = 0;
            $totalPartsCount = 0;
            $totalLength = 0;
            $usedLength = 0;

            foreach ($groups as $key => $instances) {
                [$type, $size, $grade] = explode('|', $key);

                // Find available stock for this material
                $stockItems = StockItem::where('type', $type)
                    ->where('size', $size)
                    ->where('grade', $grade)
                    ->where('status', 'free')
                    ->orderBy('length', 'desc')
                    ->get();

                $partsToNest = $instances->sortByDesc(fn ($pi) => $pi->part->length);
                $bars = [];

                foreach ($partsToNest as $pi) {
                    $partLength = $pi->part->length;
                    $nested = false;

                    // Try to fit in existing bars
                    foreach ($bars as &$barData) {
                        if (($barData['remaining'] - $partLength - $kerf) >= 0) {
                            $barData['parts'][] = $pi;
                            $barData['remaining'] -= ($partLength + $kerf);
                            $barData['used'] += $partLength; // Only count part length in used
                            $nested = true;
                            break;
                        }
                    }

                    // If not nested, pick a new bar from stock
                    if (!$nested) {
                        $stockItem = $stockItems->first(fn ($si) => $si->length >= $partLength);

                        if ($stockItem) {
                            $bars[] = [
                                'stock_item' => $stockItem,
                                'length' => (float) $stockItem->length,
                                'remaining' => (float) $stockItem->length - $partLength - $kerf,
                                'used' => (float) $partLength,
                                'parts' => [$pi],
                                'is_purchase' => false,
                            ];
                            $stockItems = $stockItems->reject(fn ($si) => $si->id === $stockItem->id);
                            $nested = true;
                        } else {
                            // If no stock found, we might need to buy a new bar
                            $defaultLength = 240.0;
                            if ($partLength > $defaultLength) {
                                $defaultLength = ceil($partLength / 12) * 12;
                            }

                            $bars[] = [
                                'stock_item' => null,
                                'length' => (float) $defaultLength,
                                'remaining' => (float) $defaultLength - $partLength - $kerf,
                                'used' => (float) $partLength,
                                'parts' => [$pi],
                                'is_purchase' => true,
                            ];
                            $nested = true;
                        }
                    }
                }

                // Save results
                foreach ($bars as $barData) {
                    $nestingBar = NestingBar::create([
                        'nesting_id' => $nesting->id,
                        'bar_number' => ++$totalBars,
                        'stock_item_id' => $barData['stock_item']?->id,
                        'is_purchase' => $barData['is_purchase'],
                        'length' => $barData['length'],
                        'quantity' => 1,
                        'used_length' => $barData['used'],
                        'remnant_length' => max(0, $barData['remaining']),
                    ]);

                    $currentPos = 0;
                    $posInBar = 1;
                    foreach ($barData['parts'] as $pi) {
                        NestingPart::create([
                            'nesting_id' => $nesting->id,
                            'nesting_bar_id' => $nestingBar->id,
                            'part_instance_id' => $pi->id,
                            'position_on_bar' => $posInBar++,
                            'cut_length' => $pi->part->length,
                            'start_position' => $currentPos,
                        ]);

                        $pi->update(['nesting_id' => $nesting->id]);
                        $currentPos += ($pi->part->length + $kerf);
                        $totalPartsCount++;
                    }

                    $totalLength += $barData['length'];
                    $usedLength += $barData['used'];
                }
            }

            $nesting->update([
                'total_bars' => $totalBars,
                'total_parts' => $totalPartsCount,
                'total_length' => $totalLength,
                'used_length' => $usedLength,
                'waste_length' => $totalLength - $usedLength,
                'efficiency_percent' => $totalLength > 0 ? ($usedLength / $totalLength) * 100 : 0,
            ]);

            return $nesting;
        });
    }

    /**
     * Approve a nesting: Marks stock as ASSIGNED.
     */
    public function approve(Nesting $nesting): void
    {
        // Eager load bars with stock items to prevent N+1 queries
        $nesting->load('bars.stockItem');

        DB::transaction(function () use ($nesting): void {
            $nesting->bars->each(function ($bar): void {
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
        // Eager load bars with stock items and nesting relationship to prevent N+1 queries
        $nesting->load('bars.stockItem');

        DB::transaction(function () use ($nesting): void {
            $nesting->bars->each(function ($bar): void {
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
            'stock_id' => 'REM-'.strtoupper(uniqid()),
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
            'notes' => 'Created as remnant from nesting '.$bar->nesting->nesting_number,
        ]);
    }
}
