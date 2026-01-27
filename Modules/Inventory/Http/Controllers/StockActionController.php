<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;
use Modules\Inventory\Http\Requests\AdjustStockRequest;
use Modules\Inventory\Http\Requests\AssignStockRequest;
use Modules\Inventory\Http\Requests\BulkStockActionRequest;
use Modules\Inventory\Http\Requests\CreateRemnantRequest;
use Modules\Inventory\Http\Requests\ScrapStockRequest;
use Modules\Inventory\Http\Requests\TransferStockRequest;
use Modules\Inventory\Models\StockItem;
use Modules\Inventory\Services\InventoryService;

class StockActionController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService,
    ) {}

    /**
     * Show the transfer form.
     */
    public function transferForm(StockItem $inventory): Response
    {
        return Inertia::render('Inventory::Actions/Transfer', [
            'stockItem' => $inventory->load(['material', 'reservedProject']),
            'stockAreas' => $this->getStockAreas(),
        ]);
    }

    /**
     * Transfer stock to a different location.
     */
    public function transfer(TransferStockRequest $request, StockItem $inventory): RedirectResponse
    {
        try {
            $this->inventoryService->transfer(
                $inventory,
                $request->validated('stock_area'),
                $request->validated('notes'),
            );

            return redirect()
                ->route('inventory.show', $inventory)
                ->with('success', 'Stock transferred successfully.');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['stock_area' => $e->getMessage()]);
        }
    }

    /**
     * Show the assign form.
     */
    public function assignForm(StockItem $inventory): Response
    {
        $projects = Project::where('status', 'active')
            ->orderBy('job_number')
            ->get(['id', 'job_number', 'name']);

        return Inertia::render('Inventory::Actions/Assign', [
            'stockItem' => $inventory->load(['material', 'reservedProject']),
            'projects' => $projects,
            'canAssign' => $this->inventoryService->canTransition($inventory->status, 'assigned'),
        ]);
    }

    /**
     * Assign stock to a project.
     */
    public function assign(AssignStockRequest $request, StockItem $inventory): RedirectResponse
    {
        try {
            $this->inventoryService->assign(
                $inventory,
                $request->validated('project_id'),
                $request->validated('notes'),
            );

            return redirect()
                ->route('inventory.show', $inventory)
                ->with('success', 'Stock assigned to project successfully.');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['project_id' => $e->getMessage()]);
        }
    }

    /**
     * Release stock from project assignment.
     */
    public function release(StockItem $inventory): RedirectResponse
    {
        try {
            $this->inventoryService->release($inventory);

            return redirect()
                ->route('inventory.show', $inventory)
                ->with('success', 'Stock released from project.');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Commit stock to production.
     */
    public function commit(StockItem $inventory): RedirectResponse
    {
        try {
            $this->inventoryService->commit($inventory);

            return redirect()
                ->route('inventory.show', $inventory)
                ->with('success', 'Stock committed to production.');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Mark stock as used.
     */
    public function markUsed(StockItem $inventory): RedirectResponse
    {
        try {
            $this->inventoryService->use($inventory);

            return redirect()
                ->route('inventory.show', $inventory)
                ->with('success', 'Stock marked as used.');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Return stock to free status.
     */
    public function return(StockItem $inventory): RedirectResponse
    {
        try {
            $this->inventoryService->return($inventory);

            return redirect()
                ->route('inventory.show', $inventory)
                ->with('success', 'Stock returned to free status.');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show the adjust form.
     */
    public function adjustForm(StockItem $inventory): Response
    {
        return Inertia::render('Inventory::Actions/Adjust', [
            'stockItem' => $inventory->load(['material', 'reservedProject']),
        ]);
    }

    /**
     * Adjust stock quantity.
     */
    public function adjust(AdjustStockRequest $request, StockItem $inventory): RedirectResponse
    {
        try {
            $this->inventoryService->adjust(
                $inventory,
                $request->validated('quantity'),
                $request->validated('reason'),
                $request->validated('notes'),
            );

            return redirect()
                ->route('inventory.show', $inventory)
                ->with('success', 'Stock quantity adjusted successfully.');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['quantity' => $e->getMessage()]);
        }
    }

    /**
     * Show the scrap form.
     */
    public function scrapForm(StockItem $inventory): Response
    {
        return Inertia::render('Inventory::Actions/Scrap', [
            'stockItem' => $inventory->load(['material', 'reservedProject']),
            'canScrap' => $this->inventoryService->canTransition($inventory->status, 'scrapped'),
        ]);
    }

    /**
     * Scrap stock item.
     */
    public function scrap(ScrapStockRequest $request, StockItem $inventory): RedirectResponse
    {
        try {
            $this->inventoryService->scrap(
                $inventory,
                $request->validated('reason'),
                $request->validated('notes'),
            );

            return redirect()
                ->route('inventory.index')
                ->with('success', 'Stock item scrapped.');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['reason' => $e->getMessage()]);
        }
    }

    /**
     * Show the remnant creation form.
     */
    public function remnantForm(StockItem $inventory): Response
    {
        return Inertia::render('Inventory::Actions/Remnant', [
            'stockItem' => $inventory->load(['material', 'reservedProject']),
        ]);
    }

    /**
     * Create a remnant from stock item.
     */
    public function createRemnant(CreateRemnantRequest $request, StockItem $inventory): RedirectResponse
    {
        try {
            $remnant = $this->inventoryService->createRemnant(
                $inventory,
                $request->validated('remnant_length'),
                $request->validated('notes'),
            );

            return redirect()
                ->route('inventory.show', $remnant)
                ->with('success', 'Remnant created successfully.');
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['remnant_length' => $e->getMessage()]);
        }
    }

    /**
     * Handle bulk stock actions.
     */
    public function bulkAction(BulkStockActionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $action = $data['action'];
        $itemIds = $data['item_ids'];
        $notes = $data['notes'] ?? null;

        try {
            $movements = match ($action) {
                'transfer' => $this->inventoryService->bulkTransfer($itemIds, $data['stock_area'], $notes),
                'assign' => $this->inventoryService->bulkAssign($itemIds, $data['project_id'], $notes),
                'release' => $this->inventoryService->bulkRelease($itemIds, $notes),
                'commit' => $this->bulkCommit($itemIds, $notes),
                'use' => $this->bulkUse($itemIds, $notes),
                'scrap' => $this->bulkScrap($itemIds, $data['reason'], $notes),
                default => throw new InvalidArgumentException('Invalid action.'),
            };

            $count = $movements->count();

            return redirect()
                ->route('inventory.index')
                ->with('success', "Bulk {$action} completed on {$count} items.");
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Bulk commit items.
     */
    protected function bulkCommit(array $itemIds, ?string $notes): \Illuminate\Support\Collection
    {
        $items = StockItem::whereIn('id', $itemIds)->get();
        $movements = collect();

        foreach ($items as $item) {
            if ($this->inventoryService->canTransition($item->status, 'committed')) {
                $movements->push($this->inventoryService->commit($item, null, null, $notes));
            }
        }

        return $movements;
    }

    /**
     * Bulk mark items as used.
     */
    protected function bulkUse(array $itemIds, ?string $notes): \Illuminate\Support\Collection
    {
        $items = StockItem::whereIn('id', $itemIds)->get();
        $movements = collect();

        foreach ($items as $item) {
            if ($this->inventoryService->canTransition($item->status, 'used')) {
                $movements->push($this->inventoryService->use($item, null, null, $notes));
            }
        }

        return $movements;
    }

    /**
     * Bulk scrap items.
     */
    protected function bulkScrap(array $itemIds, string $reason, ?string $notes): \Illuminate\Support\Collection
    {
        $items = StockItem::whereIn('id', $itemIds)->get();
        $movements = collect();

        foreach ($items as $item) {
            if ($this->inventoryService->canTransition($item->status, 'scrapped')) {
                $movements->push($this->inventoryService->scrap($item, $reason, $notes));
            }
        }

        return $movements;
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
}
