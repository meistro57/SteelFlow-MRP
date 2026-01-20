<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\GasBottleInspection;
use App\Models\GasBottleRental;
use App\Services\GasBottleService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Inventory\Models\StockItem;

class GasBottleController extends Controller
{
    public function __construct(
        protected GasBottleService $gasBottleService
    ) {}

    /**
     * Display a listing of gas bottle rentals.
     */
    public function index(Request $request): Response
    {
        $query = GasBottleRental::with(['stockItem', 'customer'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('search')) {
            $query->whereHas('stockItem', function ($q) use ($request) {
                $q->where('internal_code', 'like', '%'.$request->search.'%');
            });
        }

        $rentals = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => GasBottleRental::count(),
            'active' => GasBottleRental::where('status', 'active')->count(),
            'reserved' => GasBottleRental::where('status', 'reserved')->count(),
            'overdue' => GasBottleRental::where('status', 'active')
                ->whereNotNull('due_back_at')
                ->where('due_back_at', '<', now())
                ->count(),
        ];

        return Inertia::render('GasBottles/Index', [
            'rentals' => $rentals,
            'stats' => $stats,
            'filters' => $request->only(['status', 'customer_id', 'search']),
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'code']),
        ]);
    }

    /**
     * Show the form for creating a new gas bottle rental.
     */
    public function create(): Response
    {
        return Inertia::render('GasBottles/Create', [
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'code']),
            'bottles' => StockItem::where('type', 'gas_bottle')
                ->where('status', 'available')
                ->with('material')
                ->orderBy('internal_code')
                ->get(),
        ]);
    }

    /**
     * Store a newly created gas bottle rental.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'stock_item_id' => 'required|exists:stock_items,id',
            'customer_id' => 'required|exists:customers,id',
            'rental_type' => 'required|in:reserve,checkout',
            'project_id' => 'nullable|exists:projects,id',
            'expires_at' => 'nullable|date',
            'due_back_at' => 'required_if:rental_type,checkout|nullable|date',
            'deposit_cents' => 'required_if:rental_type,checkout|nullable|integer|min:0',
            'rental_rate_cents' => 'required_if:rental_type,checkout|nullable|integer|min:0',
            'rental_rate_period' => 'required_if:rental_type,checkout|nullable|in:day,week,month',
            'notes' => 'nullable|string|max:500',
        ]);

        $bottle = StockItem::findOrFail($validated['stock_item_id']);
        $customer = Customer::findOrFail($validated['customer_id']);

        if ($validated['rental_type'] === 'reserve') {
            $rental = $this->gasBottleService->reserveBottle(
                $bottle,
                $customer,
                $validated['expires_at'] ? Carbon::parse($validated['expires_at']) : null,
                $validated['project_id'] ?? null
            );
        } else {
            // Create reservation first
            $rental = $this->gasBottleService->reserveBottle(
                $bottle,
                $customer,
                null,
                $validated['project_id'] ?? null
            );

            // Then checkout immediately
            $rental = $this->gasBottleService->checkoutBottle(
                $rental,
                Carbon::parse($validated['due_back_at']),
                $validated['deposit_cents'],
                $validated['rental_rate_cents'],
                $validated['rental_rate_period']
            );

            if ($validated['notes']) {
                $rental->update(['notes' => $validated['notes']]);
            }
        }

        return redirect()
            ->route('gas-bottles.show', $rental)
            ->with('success', 'Gas bottle rental created successfully.');
    }

    /**
     * Display the specified gas bottle rental.
     */
    public function show(GasBottleRental $gasBottle): Response
    {
        $gasBottle->load([
            'stockItem.material',
            'customer',
            'creator',
        ]);

        $inspections = GasBottleInspection::where('stock_item_id', $gasBottle->stock_item_id)
            ->with('creator')
            ->orderByDesc('scheduled_for')
            ->get();

        return Inertia::render('GasBottles/Show', [
            'rental' => $gasBottle,
            'inspections' => $inspections,
            'availableBottles' => StockItem::where('type', 'gas_bottle')
                ->where('status', 'available')
                ->with('material')
                ->orderBy('internal_code')
                ->get(),
        ]);
    }

    /**
     * Return a gas bottle.
     */
    public function return(Request $request, GasBottleRental $gasBottle): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $this->gasBottleService->returnBottle($gasBottle, $validated['notes'] ?? null);

        return redirect()
            ->route('gas-bottles.show', $gasBottle)
            ->with('success', 'Gas bottle returned successfully.');
    }

    /**
     * Swap a gas bottle for another.
     */
    public function swap(Request $request, GasBottleRental $gasBottle): RedirectResponse
    {
        $validated = $request->validate([
            'replacement_id' => 'required|exists:stock_items,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $replacement = StockItem::findOrFail($validated['replacement_id']);
        $newRental = $this->gasBottleService->swapBottle(
            $gasBottle,
            $replacement,
            $validated['notes'] ?? null
        );

        return redirect()
            ->route('gas-bottles.show', $newRental)
            ->with('success', 'Gas bottle swapped successfully.');
    }

    /**
     * Flag a gas bottle as lost or damaged.
     */
    public function flag(Request $request, GasBottleRental $gasBottle): RedirectResponse
    {
        $validated = $request->validate([
            'flag' => 'required|in:lost,damaged',
            'notes' => 'nullable|string|max:500',
        ]);

        $this->gasBottleService->flagLossOrDamage(
            $gasBottle,
            $validated['flag'],
            $validated['notes'] ?? null
        );

        return redirect()
            ->route('gas-bottles.show', $gasBottle)
            ->with('success', 'Gas bottle flagged as '.$validated['flag'].' successfully.');
    }

    /**
     * Schedule an inspection for a gas bottle.
     */
    public function scheduleInspection(Request $request, GasBottleRental $gasBottle): RedirectResponse
    {
        $validated = $request->validate([
            'scheduled_for' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $this->gasBottleService->scheduleInspection(
            $gasBottle->stockItem,
            Carbon::parse($validated['scheduled_for']),
            null,
            $validated['notes'] ?? null
        );

        return redirect()
            ->route('gas-bottles.show', $gasBottle)
            ->with('success', 'Inspection scheduled successfully.');
    }
}
