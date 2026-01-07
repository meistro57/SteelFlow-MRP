<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockItemRequest;
use App\Http\Requests\UpdateStockItemRequest;
use App\Models\Grade;
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
        $query = StockItem::with(['material', 'reservedProject']);

        // Apply search filter
        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('stock_id', 'like', "%{$search}%")
                    ->orWhere('heat_number', 'like', "%{$search}%")
                    ->orWhere('po_number', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('size', 'like', "%{$search}%")
                    ->orWhere('grade', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if (request('status')) {
            $query->where('status', request('status'));
        }

        // Apply location filter
        if (request('location')) {
            $query->where('stock_area', request('location'));
        }

        // Apply grade filter
        if (request('grade')) {
            $query->where('grade', request('grade'));
        }

        // Apply sorting
        $sortBy = request('sort_by', 'created_at');
        $sortDirection = request('sort_direction', 'desc');

        // Validate sort direction
        $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'desc';

        // Map sortable columns
        $sortableColumns = [
            'stock_id' => 'stock_id',
            'type' => 'type',
            'grade' => 'grade',
            'length' => 'length',
            'quantity' => 'quantity',
            'status' => 'status',
            'location' => 'stock_area',
            'created_at' => 'created_at',
        ];

        $sortColumn = $sortableColumns[$sortBy] ?? 'created_at';
        $query->orderBy($sortColumn, $sortDirection);

        $statusCounts = (clone $query)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $stockItems = $query->paginate(20)->withQueryString();

        return Inertia::render('Inventory/Index', [
            'stockItems' => $stockItems,
            'filters' => request()->only(['search', 'status', 'location', 'grade', 'sort_by', 'sort_direction']),
            'statuses' => $this->getStatuses(),
            'locations' => $this->getStockAreas(),
            'grades' => $this->getUniqueGrades(),
            'statusCounts' => $statusCounts,
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
            'types' => $this->getUniqueTypes(),
            'sizesByType' => $this->getSizesByType(),
            'grades' => $this->getGrades(),
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
            'types' => $this->getUniqueTypes(),
            'sizesByType' => $this->getSizesByType(),
            'grades' => $this->getGrades(),
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

    /**
     * Get unique material types.
     */
    protected function getUniqueTypes(): array
    {
        return Material::where('is_active', true)
            ->distinct()
            ->orderBy('type')
            ->pluck('type')
            ->toArray();
    }

    /**
     * Get sizes grouped by type.
     */
    protected function getSizesByType(): array
    {
        $materials = Material::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['type', 'size_imperial']);

        $sizesByType = [];
        foreach ($materials as $material) {
            if (! isset($sizesByType[$material->type])) {
                $sizesByType[$material->type] = [];
            }
            if ($material->size_imperial && ! in_array($material->size_imperial, $sizesByType[$material->type])) {
                $sizesByType[$material->type][] = $material->size_imperial;
            }
        }

        return $sizesByType;
    }

    /**
     * Get all active grades.
     */
    protected function getGrades(): array
    {
        return Grade::where('is_active', true)
            ->orderBy('code')
            ->pluck('code')
            ->toArray();
    }

    /**
     * Get unique grades from stock items.
     */
    protected function getUniqueGrades(): array
    {
        return StockItem::distinct()
            ->orderBy('grade')
            ->pluck('grade')
            ->filter()
            ->toArray();
    }
}
