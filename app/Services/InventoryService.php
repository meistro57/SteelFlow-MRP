<?php

namespace App\Services;

use App\Models\StockItem;
use App\Models\StockMovement;
use App\Models\ReceivingRecord;
use App\Models\PurchaseOrderLine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InventoryService
{
    /**
     * Record a stock movement (audit trail).
     */
    public function recordMovement(StockItem $item, string $type, int $qty, array $data = []): StockMovement
    {
        return StockMovement::create([
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
    }

    /**
     * Process receiving of a PO line.
     */
    public function receiveLine(PurchaseOrderLine $line, array $data): ReceivingRecord
    {
        return DB::transaction(function () use ($line, $data) {
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
            }

            return $record;
        });
    }

    protected function generateStockId(): string
    {
        return 'STK-' . strtoupper(uniqid());
    }
}
