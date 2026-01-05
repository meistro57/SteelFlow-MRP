<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockItemRequest;
use App\Http\Requests\UpdateStockItemRequest;
use App\Models\Material;
use App\Models\Project;
use App\Models\StockItem;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    /**
     * Display a listing of stock items.
     */
    public function index(): Response
    {
        $stockItems = StockItem::with(['material', 'reservedProject'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Inventory/Index', [
            'stockItems' => $stockItems,
            'filters' => request()->only(['search', 'status', 'type']),
            'statuses' => $this->getStatuses(),
        ]);
    }

    /**
     * Show the form for creating a new stock item.
     */
    public function create(): Response
    {
        $materials = Material::with('grade')->orderBy('sort_order')->get(['id', 'type', 'size_imperial', 'size_metric', 'grade_id']);
        $projects = Project::where('status', 'active')->orderBy('job_number')->get(['id', 'job_number', 'name']);

        return Inertia::render('Inventory/Create', [
            'materials' => $materials,
            'projects' => $projects,
            'statuses' => $this->getStatuses(),
            'stockAreas' => $this->getStockAreas(),
        ]);
    }

    /**
     * Store a newly created stock item.
     */
    public function store(StoreStockItemRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['stock_id'] = $this->generateStockId();

        // Populate type, size, and grade from material if not provided
        if (isset($data['material_id'])) {
            $material = Material::with('grade')->find($data['material_id']);
            if ($material) {
                $data['type'] = $data['type'] ?? $material->type;
                $data['size'] = $data['size'] ?? ($material->size_imperial ?: $material->size_metric);
                $data['grade'] = $data['grade'] ?? ($material->grade->code ?? '');
            }
        }

        StockItem::create($data);

        return redirect()
            ->route('inventory.index')
            ->with('success', 'Stock item created successfully.');
    }

    /**
     * Display the specified stock item.
     */
    public function show(StockItem $inventory): Response
    {
        $inventory->load(['material', 'reservedProject', 'movements', 'parentStock', 'remnants']);

        return Inertia::render('Inventory/Show', [
            'stockItem' => $inventory,
        ]);
    }

    /**
     * Show the form for editing the specified stock item.
     */
    public function edit(StockItem $inventory): Response
    {
        $materials = Material::with('grade')->orderBy('sort_order')->get(['id', 'type', 'size_imperial', 'size_metric', 'grade_id']);
        $projects = Project::orderBy('job_number')->get(['id', 'job_number', 'name']);

        return Inertia::render('Inventory/Edit', [
            'stockItem' => $inventory,
            'materials' => $materials,
            'projects' => $projects,
            'statuses' => $this->getStatuses(),
            'stockAreas' => $this->getStockAreas(),
        ]);
    }

    /**
     * Update the specified stock item.
     */
    public function update(UpdateStockItemRequest $request, StockItem $inventory): RedirectResponse
    {
        $data = $request->validated();

        // Populate type, size, and grade from material if not provided
        if (isset($data['material_id'])) {
            $material = Material::with('grade')->find($data['material_id']);
            if ($material) {
                $data['type'] = $data['type'] ?? $material->type;
                $data['size'] = $data['size'] ?? ($material->size_imperial ?: $material->size_metric);
                $data['grade'] = $data['grade'] ?? ($material->grade->code ?? '');
            }
        }

        $inventory->update($data);

        return redirect()
            ->route('inventory.show', $inventory)
            ->with('success', 'Stock item updated successfully.');
    }

    /**
     * Remove the specified stock item.
     */
    public function destroy(StockItem $inventory): RedirectResponse
    {
        $inventory->delete();

        return redirect()
            ->route('inventory.index')
            ->with('success', 'Stock item deleted successfully.');
    }

    /**
     * Get available statuses.
     */
    protected function getStatuses(): array
    {
        return [
            'free' => 'Free',
            'assigned' => 'Assigned',
            'committed' => 'Committed',
            'used' => 'Used',
        ];
    }

    /**
     * Get available stock areas.
     */
    protected function getStockAreas(): array
    {
        return [
            'yard_a' => 'Yard A',
            'yard_b' => 'Yard B',
            'warehouse' => 'Warehouse',
            'production' => 'Production Floor',
            'shipping' => 'Shipping Area',
        ];
    }

    /**
     * Generate a unique stock ID.
     */
    protected function generateStockId(): string
    {
        return 'STK-'.strtoupper(uniqid());
    }
}
