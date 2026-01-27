<?php

// app/Services/InventoryService.php

namespace Modules\Inventory\Services;

use App\Models\Project;
use App\Models\PurchaseOrderLine;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Modules\Documents\Services\DocumentService;
use Modules\Inventory\Models\ReceivingRecord;
use Modules\Inventory\Models\StockItem;
use Modules\Inventory\Models\StockMovement;

class InventoryService
{
    /**
     * Valid status transitions for stock items.
     */
    protected array $validTransitions = [
        'free' => ['assigned', 'committed', 'used', 'scrapped'],
        'assigned' => ['free', 'committed', 'used', 'scrapped'],
        'committed' => ['free', 'assigned', 'used', 'scrapped'],
        'used' => ['free', 'scrapped'], // Return path available
        'scrapped' => [], // Terminal state
    ];

    /**
     * Record a stock movement (audit trail).
     */
    public function recordMovement(StockItem $item, string $type, int $qty, array $data = []): StockMovement
    {
        $movement = StockMovement::create([
            'stock_item_id' => $item->id,
            'movement_type' => $type,
            'quantity' => $qty,
            'from_status' => $data['from_status'] ?? $item->getOriginal('status'),
            'to_status' => $data['to_status'] ?? $item->status,
            'from_area' => $data['from_area'] ?? $item->getOriginal('stock_area'),
            'to_area' => $data['to_area'] ?? $item->stock_area,
            'reference_type' => $data['reference_type'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        Log::info('Stock movement recorded', [
            'movement_id' => $movement->id,
            'stock_item_id' => $item->id,
            'movement_type' => $type,
            'quantity' => $qty,
            'context' => $data,
        ]);

        return $movement;
    }

    /**
     * Transfer stock item to a different location.
     */
    public function transfer(StockItem $item, string $toArea, ?string $notes = null): StockMovement
    {
        $operationId = Str::uuid()->toString();
        $fromArea = $item->stock_area;

        if ($fromArea === $toArea) {
            throw new InvalidArgumentException('Stock item is already in the destination area.');
        }

        Log::info('Starting stock transfer', [
            'operation_id' => $operationId,
            'stock_item_id' => $item->id,
            'from_area' => $fromArea,
            'to_area' => $toArea,
        ]);

        return DB::transaction(function () use ($item, $toArea, $fromArea, $notes) {
            $item->update(['stock_area' => $toArea]);

            return $this->recordMovement($item, 'transfer', $item->quantity, [
                'from_area' => $fromArea,
                'to_area' => $toArea,
                'from_status' => $item->status,
                'to_status' => $item->status,
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Assign stock item to a project.
     */
    public function assign(StockItem $item, int $projectId, ?string $notes = null): StockMovement
    {
        $this->validateStatusTransition($item->status, 'assigned');

        return DB::transaction(function () use ($item, $projectId, $notes) {
            $fromStatus = $item->status;

            $item->update([
                'status' => 'assigned',
                'reserved_project_id' => $projectId,
            ]);

            return $this->recordMovement($item, 'assign', $item->quantity, [
                'from_status' => $fromStatus,
                'to_status' => 'assigned',
                'reference_type' => 'project',
                'reference_id' => $projectId,
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Release stock item from project assignment.
     */
    public function release(StockItem $item, ?string $notes = null): StockMovement
    {
        $this->validateStatusTransition($item->status, 'free');

        return DB::transaction(function () use ($item, $notes) {
            $fromStatus = $item->status;
            $projectId = $item->reserved_project_id;

            $item->update([
                'status' => 'free',
                'reserved_project_id' => null,
            ]);

            return $this->recordMovement($item, 'release', $item->quantity, [
                'from_status' => $fromStatus,
                'to_status' => 'free',
                'reference_type' => $projectId ? 'project' : null,
                'reference_id' => $projectId,
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Commit stock item to production/nesting.
     */
    public function commit(StockItem $item, ?int $referenceId = null, ?string $referenceType = null, ?string $notes = null): StockMovement
    {
        $this->validateStatusTransition($item->status, 'committed');

        return DB::transaction(function () use ($item, $referenceId, $referenceType, $notes) {
            $fromStatus = $item->status;

            $item->update(['status' => 'committed']);

            return $this->recordMovement($item, 'commit', $item->quantity, [
                'from_status' => $fromStatus,
                'to_status' => 'committed',
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Mark stock item as used (consumed in production).
     */
    public function use(StockItem $item, ?int $referenceId = null, ?string $referenceType = null, ?string $notes = null): StockMovement
    {
        $this->validateStatusTransition($item->status, 'used');

        return DB::transaction(function () use ($item, $referenceId, $referenceType, $notes) {
            $fromStatus = $item->status;

            $item->update(['status' => 'used']);

            return $this->recordMovement($item, 'use', $item->quantity, [
                'from_status' => $fromStatus,
                'to_status' => 'used',
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Return stock item back to free status.
     */
    public function return(StockItem $item, ?string $notes = null): StockMovement
    {
        $this->validateStatusTransition($item->status, 'free');

        return DB::transaction(function () use ($item, $notes) {
            $fromStatus = $item->status;

            $item->update([
                'status' => 'free',
                'reserved_project_id' => null,
            ]);

            return $this->recordMovement($item, 'return', $item->quantity, [
                'from_status' => $fromStatus,
                'to_status' => 'free',
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Adjust stock item quantity (for physical count corrections).
     */
    public function adjust(StockItem $item, int $newQuantity, string $reason, ?string $notes = null): StockMovement
    {
        $oldQuantity = $item->quantity;
        $difference = $newQuantity - $oldQuantity;

        if ($difference === 0) {
            throw new InvalidArgumentException('New quantity is the same as current quantity.');
        }

        if ($newQuantity < 0) {
            throw new InvalidArgumentException('Quantity cannot be negative.');
        }

        return DB::transaction(function () use ($item, $newQuantity, $difference, $reason, $notes) {
            $item->update(['quantity' => $newQuantity]);

            $movementType = $difference > 0 ? 'adjustment_add' : 'adjustment_remove';

            return $this->recordMovement($item, $movementType, abs($difference), [
                'from_status' => $item->status,
                'to_status' => $item->status,
                'notes' => "Reason: {$reason}".($notes ? " | {$notes}" : ''),
            ]);
        });
    }

    /**
     * Scrap/write-off stock item.
     */
    public function scrap(StockItem $item, string $reason, ?string $notes = null): StockMovement
    {
        $this->validateStatusTransition($item->status, 'scrapped');

        return DB::transaction(function () use ($item, $reason, $notes) {
            $fromStatus = $item->status;

            $item->update(['status' => 'scrapped']);

            return $this->recordMovement($item, 'scrap', $item->quantity, [
                'from_status' => $fromStatus,
                'to_status' => 'scrapped',
                'notes' => "Reason: {$reason}".($notes ? " | {$notes}" : ''),
            ]);
        });
    }

    /**
     * Create a remnant from a stock item.
     */
    public function createRemnant(StockItem $parentItem, float $remnantLength, ?string $notes = null): StockItem
    {
        if ($remnantLength <= 0) {
            throw new InvalidArgumentException('Remnant length must be positive.');
        }

        if ($remnantLength >= $parentItem->length) {
            throw new InvalidArgumentException('Remnant length must be less than parent item length.');
        }

        return DB::transaction(function () use ($parentItem, $remnantLength, $notes) {
            // Create the remnant
            $remnant = StockItem::create([
                'stock_id' => $this->generateStockId(),
                'material_id' => $parentItem->material_id,
                'upf_price_id' => $parentItem->upf_price_id,
                'type' => $parentItem->type,
                'size' => $parentItem->size,
                'grade' => $parentItem->grade,
                'length' => $remnantLength,
                'quantity' => 1,
                'status' => 'free',
                'stock_area' => $parentItem->stock_area,
                'heat_number' => $parentItem->heat_number,
                'po_number' => $parentItem->po_number,
                'country_of_origin' => $parentItem->country_of_origin,
                'cost_per_unit' => $parentItem->cost_per_unit,
                'is_remnant' => true,
                'parent_stock_id' => $parentItem->id,
            ]);

            // Record movement for the remnant creation
            $this->recordMovement($remnant, 'remnant_create', 1, [
                'from_status' => null,
                'to_status' => 'free',
                'reference_type' => 'stock_item',
                'reference_id' => $parentItem->id,
                'notes' => $notes,
            ]);

            // Update parent item length
            $newParentLength = $parentItem->length - $remnantLength;
            $parentItem->update(['length' => $newParentLength]);

            $this->recordMovement($parentItem, 'remnant_split', $parentItem->quantity, [
                'notes' => "Created remnant {$remnant->stock_id} with length {$remnantLength}".($notes ? " | {$notes}" : ''),
            ]);

            return $remnant;
        });
    }

    /**
     * Bulk transfer multiple stock items.
     */
    public function bulkTransfer(array $itemIds, string $toArea, ?string $notes = null): Collection
    {
        $items = StockItem::whereIn('id', $itemIds)->get();
        $movements = collect();

        DB::transaction(function () use ($items, $toArea, $notes, &$movements) {
            foreach ($items as $item) {
                if ($item->stock_area !== $toArea) {
                    $movements->push($this->transfer($item, $toArea, $notes));
                }
            }
        });

        return $movements;
    }

    /**
     * Bulk assign multiple stock items to a project.
     */
    public function bulkAssign(array $itemIds, int $projectId, ?string $notes = null): Collection
    {
        $items = StockItem::whereIn('id', $itemIds)->get();
        $movements = collect();

        DB::transaction(function () use ($items, $projectId, $notes, &$movements) {
            foreach ($items as $item) {
                if ($this->canTransition($item->status, 'assigned')) {
                    $movements->push($this->assign($item, $projectId, $notes));
                }
            }
        });

        return $movements;
    }

    /**
     * Bulk release multiple stock items.
     */
    public function bulkRelease(array $itemIds, ?string $notes = null): Collection
    {
        $items = StockItem::whereIn('id', $itemIds)->get();
        $movements = collect();

        DB::transaction(function () use ($items, $notes, &$movements) {
            foreach ($items as $item) {
                if ($this->canTransition($item->status, 'free')) {
                    $movements->push($this->release($item, $notes));
                }
            }
        });

        return $movements;
    }

    /**
     * Get stock valuation summary.
     */
    public function getValuation(array $filters = []): array
    {
        $query = StockItem::query()
            ->whereNull('deleted_at')
            ->where('status', '!=', 'scrapped');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['stock_area'])) {
            $query->where('stock_area', $filters['stock_area']);
        }

        if (isset($filters['project_id'])) {
            $query->where('reserved_project_id', $filters['project_id']);
        }

        $items = $query->get();

        $totalPieces = $items->sum('quantity');
        $totalValue = $items->sum(fn ($item) => ($item->cost_per_unit ?? 0) * $item->quantity);
        $totalLength = $items->sum(fn ($item) => $item->length * $item->quantity);

        $byStatus = $items->groupBy('status')->map(function ($group) {
            return [
                'pieces' => $group->sum('quantity'),
                'value' => $group->sum(fn ($item) => ($item->cost_per_unit ?? 0) * $item->quantity),
                'length' => $group->sum(fn ($item) => $item->length * $item->quantity),
            ];
        });

        $byType = $items->groupBy('type')->map(function ($group) {
            return [
                'pieces' => $group->sum('quantity'),
                'value' => $group->sum(fn ($item) => ($item->cost_per_unit ?? 0) * $item->quantity),
                'length' => $group->sum(fn ($item) => $item->length * $item->quantity),
            ];
        });

        $byArea = $items->groupBy('stock_area')->map(function ($group) {
            return [
                'pieces' => $group->sum('quantity'),
                'value' => $group->sum(fn ($item) => ($item->cost_per_unit ?? 0) * $item->quantity),
                'length' => $group->sum(fn ($item) => $item->length * $item->quantity),
            ];
        });

        return [
            'total_pieces' => $totalPieces,
            'total_value' => round($totalValue, 2),
            'total_length' => round($totalLength, 2),
            'by_status' => $byStatus->toArray(),
            'by_type' => $byType->toArray(),
            'by_area' => $byArea->toArray(),
        ];
    }

    /**
     * Validate status transition.
     */
    protected function validateStatusTransition(string $fromStatus, string $toStatus): void
    {
        if (! $this->canTransition($fromStatus, $toStatus)) {
            throw new InvalidArgumentException(
                "Invalid status transition from '{$fromStatus}' to '{$toStatus}'.",
            );
        }
    }

    /**
     * Check if a status transition is valid.
     */
    public function canTransition(string $fromStatus, string $toStatus): bool
    {
        if (! isset($this->validTransitions[$fromStatus])) {
            return false;
        }

        return in_array($toStatus, $this->validTransitions[$fromStatus], true);
    }

    /**
     * Get valid transitions for a given status.
     */
    public function getValidTransitions(string $status): array
    {
        return $this->validTransitions[$status] ?? [];
    }

    /**
     * Process receiving of a PO line.
     */
    public function receiveLine(PurchaseOrderLine $line, array $data): ReceivingRecord
    {
        $operationId = Str::uuid()->toString();

        Log::info('Starting PO line receiving', [
            'operation_id' => $operationId,
            'purchase_order_id' => $line->purchase_order_id,
            'line_id' => $line->id,
            'quantity' => $data['quantity'],
        ]);

        try {
            return DB::transaction(function () use ($line, $data, $operationId) {
                // 1. Create receiving record
                $record = ReceivingRecord::create([
                    'purchase_order_line_id' => $line->id,
                    'quantity_received' => $data['quantity'],
                    'receive_date' => $data['receive_date'] ?? now(),
                    'heat_number' => $data['heat_number'] ?? null,
                    'mill_cert_number' => $data['mill_cert_number'] ?? null,
                    'country_of_origin' => $data['country_of_origin'] ?? null,
                    'stock_area' => $data['stock_area'] ?? null,
                    'received_by' => Auth::id(),
                    'notes' => $data['notes'] ?? null,
                ]);

                // Handle Mill Cert File if provided
                if (isset($data['mill_cert_file'])) {
                    $docService = app(DocumentService::class);
                    $docService->store($data['mill_cert_file'], [
                        'name' => 'Mill_Cert_'.($data['heat_number'] ?? 'Unknown'),
                        'file_type' => 'pdf',
                        'documentable_id' => $record->id,
                        'documentable_type' => ReceivingRecord::class,
                    ]);
                }

                // 2. Update PO line quantity
                $line->increment('quantity_received', $data['quantity']);

                // 3. Create stock items
                for ($i = 0; $i < $data['quantity']; $i++) {
                    $stock = StockItem::create([
                        'stock_id' => $this->generateStockId(),
                        'material_id' => $line->material_id,
                        'type' => $line->type,
                        'size' => $line->size,
                        'grade' => $line->grade,
                        'length' => $line->length,
                        'quantity' => 1,
                        'status' => 'free',
                        'reserved_project_id' => $line->purchaseOrder->project_id,
                        'stock_area' => $data['stock_area'] ?? null,
                        'heat_number' => $data['heat_number'] ?? null,
                        'po_number' => $line->purchaseOrder->po_number,
                        'receive_date' => $data['receive_date'] ?? now(),
                    ]);

                    // 4. Record movement
                    $this->recordMovement($stock, 'receive', 1, [
                        'reference_type' => 'po',
                        'reference_id' => $line->purchase_order_id,
                    ]);

                    Log::info('Stock item created from receiving', [
                        'operation_id' => $operationId,
                        'stock_item_id' => $stock->id,
                        'po_number' => $line->purchaseOrder->po_number,
                        'heat_number' => $data['heat_number'] ?? null,
                    ]);
                }

                return $record;
            });
        } catch (\Throwable $exception) {
            Log::error('Receiving transaction failed', [
                'operation_id' => $operationId,
                'purchase_order_id' => $line->purchase_order_id,
                'line_id' => $line->id,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    protected function generateStockId(): string
    {
        return 'STK-'.strtoupper(uniqid());
    }
}
