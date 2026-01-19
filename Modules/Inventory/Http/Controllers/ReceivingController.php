<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Inventory\Services\InventoryService;

class ReceivingController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService,
    ) {}

    public function create(PurchaseOrder $purchaseOrder): Response
    {
        $purchaseOrder->load(['vendor', 'project', 'lines.material']);

        return Inertia::render('Inventory::Receiving/Create', [
            'purchaseOrder' => $purchaseOrder,
        ]);
    }

    public function store(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $validated = $request->validate([
            'lines' => 'required|array',
            'lines.*.id' => 'required|exists:purchase_order_lines,id',
            'lines.*.quantity_received' => 'required|numeric|min:0',
            'lines.*.heat_number' => 'nullable|string',
            'lines.*.stock_area' => 'nullable|string',
            'receive_date' => 'required|date',
        ]);

        foreach ($validated['lines'] as $lineData) {
            if ($lineData['quantity_received'] > 0) {
                $line = PurchaseOrderLine::find($lineData['id']);
                $this->inventoryService->receiveLine($line, [
                    'quantity' => $lineData['quantity_received'],
                    'receive_date' => $validated['receive_date'],
                    'heat_number' => $lineData['heat_number'] ?? null,
                    'stock_area' => $lineData['stock_area'] ?? null,
                ]);
            }
        }

        return redirect()
            ->route('purchase-orders.show', $purchaseOrder->id)
            ->with('success', 'Materials received successfully.');
    }
}
