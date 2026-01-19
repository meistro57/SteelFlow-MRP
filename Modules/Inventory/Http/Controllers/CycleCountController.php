<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Inventory\Models\CycleCount;
use Modules\Inventory\Models\CycleCountLine;
use Modules\Inventory\Models\StockItem;
use Modules\Inventory\Services\InventoryService;

class CycleCountController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Display a listing of cycle counts.
     */
    public function index(): Response
    {
        $cycleCounts = CycleCount::with(['assignedUser', 'creator'])
            ->withCount('lines')
            ->orderByDesc('created_at')
            ->paginate(20);

        return Inertia::render('Inventory::CycleCounts/Index', [
            'cycleCounts' => $cycleCounts,
            'statusOptions' => $this->getStatusOptions(),
        ]);
    }

    /**
     * Show the form for creating a new cycle count.
     */
    public function create(): Response
    {
        $users = User::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Inventory::CycleCounts/Create', [
            'stockAreas' => $this->getStockAreas(),
            'types' => $this->getUniqueTypes(),
            'grades' => $this->getUniqueGrades(),
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created cycle count.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'stock_area' => ['nullable', 'string', 'max:50'],
            'type_filter' => ['nullable', 'string', 'max:50'],
            'grade_filter' => ['nullable', 'string', 'max:50'],
            'scheduled_date' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        return DB::transaction(function () use ($validated) {
            // Create the cycle count
            $cycleCount = CycleCount::create([
                'count_number' => $this->generateCountNumber(),
                'status' => 'draft',
                'stock_area' => $validated['stock_area'] ?? null,
                'type_filter' => $validated['type_filter'] ?? null,
                'grade_filter' => $validated['grade_filter'] ?? null,
                'scheduled_date' => $validated['scheduled_date'] ?? null,
                'assigned_to' => $validated['assigned_to'] ?? null,
                'created_by' => Auth::id(),
                'notes' => $validated['notes'] ?? null,
            ]);

            // Generate lines based on filters
            $query = StockItem::where('status', '!=', 'scrapped')
                ->whereNull('deleted_at');

            if ($cycleCount->stock_area) {
                $query->where('stock_area', $cycleCount->stock_area);
            }

            if ($cycleCount->type_filter) {
                $query->where('type', $cycleCount->type_filter);
            }

            if ($cycleCount->grade_filter) {
                $query->where('grade', $cycleCount->grade_filter);
            }

            $stockItems = $query->get();

            foreach ($stockItems as $item) {
                CycleCountLine::create([
                    'cycle_count_id' => $cycleCount->id,
                    'stock_item_id' => $item->id,
                    'expected_quantity' => $item->quantity,
                ]);
            }

            return redirect()
                ->route('cycle-counts.show', $cycleCount)
                ->with('success', "Cycle count {$cycleCount->count_number} created with {$stockItems->count()} items.");
        });
    }

    /**
     * Display the specified cycle count.
     */
    public function show(CycleCount $cycleCount): Response
    {
        $cycleCount->load([
            'lines.stockItem',
            'assignedUser',
            'creator',
            'completedByUser',
        ]);

        $summary = [
            'total_items' => $cycleCount->lines->count(),
            'counted_items' => $cycleCount->lines->where('is_counted', true)->count(),
            'variance_items' => $cycleCount->lines->filter(fn ($line) => $line->has_variance)->count(),
            'reconciled_items' => $cycleCount->lines->where('is_reconciled', true)->count(),
        ];

        return Inertia::render('Inventory::CycleCounts/Show', [
            'cycleCount' => $cycleCount,
            'summary' => $summary,
        ]);
    }

    /**
     * Show the counting form.
     */
    public function countForm(CycleCount $cycleCount): Response
    {
        if (! in_array($cycleCount->status, ['draft', 'in_progress'])) {
            abort(403, 'This cycle count cannot be modified.');
        }

        $cycleCount->load(['lines.stockItem']);

        // Start the count if not already started
        if ($cycleCount->status === 'draft') {
            $cycleCount->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);
        }

        return Inertia::render('Inventory::CycleCounts/Count', [
            'cycleCount' => $cycleCount,
        ]);
    }

    /**
     * Record count for a line item.
     */
    public function recordCount(Request $request, CycleCount $cycleCount): RedirectResponse
    {
        if (! in_array($cycleCount->status, ['draft', 'in_progress'])) {
            return back()->withErrors(['error' => 'This cycle count cannot be modified.']);
        }

        $validated = $request->validate([
            'line_id' => ['required', 'exists:cycle_count_lines,id'],
            'counted_quantity' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $line = CycleCountLine::findOrFail($validated['line_id']);

        if ($line->cycle_count_id !== $cycleCount->id) {
            return back()->withErrors(['error' => 'Invalid line item.']);
        }

        $line->update([
            'counted_quantity' => $validated['counted_quantity'],
            'is_counted' => true,
            'counted_by' => Auth::id(),
            'counted_at' => now(),
            'notes' => $validated['notes'] ?? null,
        ]);

        // Update cycle count status if not already in progress
        if ($cycleCount->status === 'draft') {
            $cycleCount->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);
        }

        return back()->with('success', 'Count recorded successfully.');
    }

    /**
     * Reconcile variances.
     */
    public function reconcile(Request $request, CycleCount $cycleCount): RedirectResponse
    {
        $validated = $request->validate([
            'reconciliations' => ['required', 'array'],
            'reconciliations.*.line_id' => ['required', 'exists:cycle_count_lines,id'],
            'reconciliations.*.action' => ['required', 'in:accept,adjust,ignore'],
            'reconciliations.*.variance_reason' => ['nullable', 'string', 'max:255'],
        ]);

        return DB::transaction(function () use ($validated, $cycleCount) {
            foreach ($validated['reconciliations'] as $reconciliation) {
                $line = CycleCountLine::findOrFail($reconciliation['line_id']);

                if ($line->cycle_count_id !== $cycleCount->id) {
                    continue;
                }

                if (! $line->is_counted || ! $line->has_variance) {
                    continue;
                }

                $action = $reconciliation['action'];

                if ($action === 'adjust') {
                    // Apply the adjustment to inventory
                    $stockItem = $line->stockItem;
                    $this->inventoryService->adjust(
                        $stockItem,
                        $line->counted_quantity,
                        'Cycle count adjustment',
                        "Cycle count {$cycleCount->count_number}: ".($reconciliation['variance_reason'] ?? 'No reason provided')
                    );
                }

                $line->update([
                    'is_reconciled' => true,
                    'variance_reason' => $reconciliation['variance_reason'] ?? null,
                ]);
            }

            return back()->with('success', 'Reconciliation completed.');
        });
    }

    /**
     * Complete the cycle count.
     */
    public function complete(CycleCount $cycleCount): RedirectResponse
    {
        // Check all items are counted
        $uncountedLines = $cycleCount->lines()->where('is_counted', false)->count();

        if ($uncountedLines > 0) {
            return back()->withErrors(['error' => "Cannot complete: {$uncountedLines} items have not been counted."]);
        }

        // Check all variances are reconciled
        $unreconciledVariances = $cycleCount->lines()
            ->where('is_counted', true)
            ->whereColumn('counted_quantity', '!=', 'expected_quantity')
            ->where('is_reconciled', false)
            ->count();

        if ($unreconciledVariances > 0) {
            return back()->withErrors(['error' => "Cannot complete: {$unreconciledVariances} variances have not been reconciled."]);
        }

        $cycleCount->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => Auth::id(),
        ]);

        return redirect()
            ->route('cycle-counts.show', $cycleCount)
            ->with('success', 'Cycle count completed successfully.');
    }

    /**
     * Generate a unique count number.
     */
    protected function generateCountNumber(): string
    {
        $prefix = 'CC-'.date('Ymd').'-';
        $lastCount = CycleCount::where('count_number', 'like', $prefix.'%')
            ->orderByDesc('count_number')
            ->first();

        if ($lastCount) {
            $lastNumber = (int) substr($lastCount->count_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix.$newNumber;
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
     * Get status options.
     */
    protected function getStatusOptions(): array
    {
        return [
            'draft' => 'Draft',
            'in_progress' => 'In Progress',
            'pending_review' => 'Pending Review',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
    }

    /**
     * Get unique types from stock items.
     */
    protected function getUniqueTypes(): array
    {
        return StockItem::whereNull('deleted_at')
            ->where('status', '!=', 'scrapped')
            ->distinct()
            ->orderBy('type')
            ->pluck('type')
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Get unique grades from stock items.
     */
    protected function getUniqueGrades(): array
    {
        return StockItem::whereNull('deleted_at')
            ->where('status', '!=', 'scrapped')
            ->distinct()
            ->orderBy('grade')
            ->pluck('grade')
            ->filter()
            ->values()
            ->toArray();
    }
}
