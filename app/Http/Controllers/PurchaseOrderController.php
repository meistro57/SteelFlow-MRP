<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseOrderRequest;
use App\Models\Project;
use App\Models\PurchaseOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Inventory\Models\Material;
use Modules\Inventory\Models\Vendor;

class PurchaseOrderController extends Controller
{
    public function index(): Response
    {
        $purchaseOrders = PurchaseOrder::with(['vendor', 'project'])
            ->orderBy('order_date', 'desc')
            ->paginate(15);

        return Inertia::render('PurchaseOrders/Index', [
            'purchaseOrders' => $purchaseOrders,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('PurchaseOrders/Create', [
            'vendors' => Vendor::orderBy('name')->get(),
            'projects' => Project::where('status', 'active')->orderBy('job_number')->get(),
            'materials' => Material::orderBy('type')->get(),
        ]);
    }

    public function store(StorePurchaseOrderRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $data = $request->validated();
            $lines = $data['lines'];
            unset($data['lines']);

            $subtotal = collect($lines)->sum(fn ($line) => $line['quantity'] * $line['unit_price']);
            $data['subtotal'] = $subtotal;
            $data['total'] = $subtotal + ($data['tax'] ?? 0) + ($data['freight'] ?? 0);
            $data['status'] = 'draft';
            $data['created_by'] = auth()->id();

            $purchaseOrder = PurchaseOrder::create($data);

            foreach ($lines as $index => $line) {
                $purchaseOrder->lines()->create([
                    'line_number' => $index + 1,
                    'material_id' => $line['material_id'],
                    'type' => $line['type'],
                    'size' => $line['size'],
                    'grade' => $line['grade'],
                    'length' => $line['length'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'extended_price' => $line['quantity'] * $line['unit_price'],
                ]);
            }
        });

        return redirect()
            ->route('purchase-orders.index')
            ->with('success', 'Purchase Order created successfully.');
    }

    public function show(PurchaseOrder $purchaseOrder): Response
    {
        $purchaseOrder->load(['vendor', 'project', 'lines.material', 'lines.receivingRecords']);

        return Inertia::render('PurchaseOrders/Show', [
            'purchaseOrder' => $purchaseOrder,
        ]);
    }

    public function edit(PurchaseOrder $purchaseOrder): Response
    {
        $purchaseOrder->load('lines.material');

        return Inertia::render('PurchaseOrders/Edit', [
            'purchaseOrder' => $purchaseOrder,
            'vendors' => Vendor::orderBy('name')->get(),
            'projects' => Project::where('status', 'active')->orderBy('job_number')->get(),
            'materials' => Material::orderBy('type')->get(),
        ]);
    }

    public function update(StorePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        DB::transaction(function () use ($request, $purchaseOrder): void {
            $data = $request->validated();
            $lines = $data['lines'];
            unset($data['lines']);

            $subtotal = collect($lines)->sum(fn ($line) => $line['quantity'] * $line['unit_price']);
            $data['subtotal'] = $subtotal;
            $data['total'] = $subtotal + ($data['tax'] ?? 0) + ($data['freight'] ?? 0);
            $data['updated_by'] = auth()->id();

            $purchaseOrder->update($data);

            // Simple approach: delete and recreation lines
            // In production, we might want to be more careful if pieces are already received
            $purchaseOrder->lines()->delete();

            foreach ($lines as $index => $line) {
                $purchaseOrder->lines()->create([
                    'line_number' => $index + 1,
                    'material_id' => $line['material_id'],
                    'type' => $line['type'],
                    'size' => $line['size'],
                    'grade' => $line['grade'],
                    'length' => $line['length'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'extended_price' => $line['quantity'] * $line['unit_price'],
                ]);
            }
        });

        return redirect()
            ->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase Order updated successfully.');
    }

    public function destroy(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $purchaseOrder->delete();

        return redirect()
            ->route('purchase-orders.index')
            ->with('success', 'Purchase Order deleted successfully.');
    }
}
