<?php

namespace App\Http\Controllers;

use App\Models\AssemblyInstance;
use App\Models\Load;
use App\Models\Project;
use App\Services\ShippingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ShippingController extends Controller
{
    public function __construct(
        protected ShippingService $shippingService
    ) {}

    /**
     * Display a listing of loads.
     */
    public function index(Request $request): Response
    {
        $query = Load::with(['project', 'items'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('load_number', 'like', '%'.$request->search.'%')
                    ->orWhere('bol_number', 'like', '%'.$request->search.'%');
            });
        }

        $loads = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => Load::count(),
            'draft' => Load::where('status', 'draft')->count(),
            'planned' => Load::where('status', 'planned')->count(),
            'in_transit' => Load::where('status', 'in_transit')->count(),
            'delivered' => Load::where('status', 'delivered')->count(),
        ];

        return Inertia::render('Shipping/Index', [
            'loads' => $loads,
            'stats' => $stats,
            'filters' => $request->only(['status', 'project_id', 'search']),
            'projects' => Project::orderBy('job_number')->get(['id', 'job_number', 'name']),
        ]);
    }

    /**
     * Show the form for creating a new load.
     */
    public function create(): Response
    {
        return Inertia::render('Shipping/Create', [
            'projects' => Project::whereIn('status', ['active', 'production'])
                ->orderBy('job_number')
                ->get(['id', 'job_number', 'name']),
        ]);
    }

    /**
     * Store a newly created load.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'destination' => 'nullable|string|max:255',
            'ship_date' => 'nullable|date',
            'carrier' => 'nullable|string|max:255',
            'truck_number' => 'nullable|string|max:50',
            'trailer_number' => 'nullable|string|max:50',
            'driver_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $load = DB::transaction(function () use ($validated) {
            return Load::create([
                'load_number' => $this->generateLoadNumber(),
                'project_id' => $validated['project_id'],
                'status' => 'draft',
                'destination' => $validated['destination'] ?? null,
                'ship_date' => $validated['ship_date'] ?? null,
                'carrier' => $validated['carrier'] ?? null,
                'truck_number' => $validated['truck_number'] ?? null,
                'trailer_number' => $validated['trailer_number'] ?? null,
                'driver_name' => $validated['driver_name'] ?? null,
                'total_weight_lbs' => 0,
                'total_weight_kg' => 0,
                'total_pieces' => 0,
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()
            ->route('shipping.show', $load)
            ->with('success', 'Load created successfully.');
    }

    /**
     * Display the specified load.
     */
    public function show(Load $load): Response
    {
        $load->load([
            'project',
            'items.assemblyInstance.assembly',
        ]);

        // Get available assembly instances for this project
        $availableInstances = AssemblyInstance::where('project_id', $load->project_id)
            ->whereIn('status', ['complete', 'ready_to_ship'])
            ->whereNull('load_id')
            ->with('assembly')
            ->orderBy('mark')
            ->get();

        return Inertia::render('Shipping/Show', [
            'load' => $load,
            'availableInstances' => $availableInstances,
        ]);
    }

    /**
     * Show the form for editing the specified load.
     */
    public function edit(Load $load): Response
    {
        return Inertia::render('Shipping/Edit', [
            'load' => $load,
            'projects' => Project::orderBy('job_number')->get(['id', 'job_number', 'name']),
        ]);
    }

    /**
     * Update the specified load.
     */
    public function update(Request $request, Load $load): RedirectResponse
    {
        $validated = $request->validate([
            'destination' => 'nullable|string|max:255',
            'ship_date' => 'nullable|date',
            'carrier' => 'nullable|string|max:255',
            'truck_number' => 'nullable|string|max:50',
            'trailer_number' => 'nullable|string|max:50',
            'driver_name' => 'nullable|string|max:255',
            'bol_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        $load->update($validated);

        return redirect()
            ->route('shipping.show', $load)
            ->with('success', 'Load updated successfully.');
    }

    /**
     * Remove the specified load.
     */
    public function destroy(Load $load): RedirectResponse
    {
        if ($load->status !== 'draft') {
            return redirect()
                ->back()
                ->with('error', 'Only draft loads can be deleted.');
        }

        $load->delete();

        return redirect()
            ->route('shipping.index')
            ->with('success', 'Load deleted successfully.');
    }

    /**
     * Add an assembly instance to the load.
     */
    public function addItem(Request $request, Load $load): RedirectResponse
    {
        $validated = $request->validate([
            'assembly_instance_id' => 'required|exists:assembly_instances,id',
        ]);

        $instance = AssemblyInstance::findOrFail($validated['assembly_instance_id']);

        $this->shippingService->addItemToLoad($load, $instance);

        return redirect()
            ->back()
            ->with('success', 'Item added to load successfully.');
    }

    /**
     * Remove an item from the load.
     */
    public function removeItem(Load $load, int $itemId): RedirectResponse
    {
        $item = $load->items()->findOrFail($itemId);

        DB::transaction(function () use ($load, $item) {
            // Update assembly instance
            $item->assemblyInstance->update([
                'status' => 'complete',
                'load_id' => null,
            ]);

            // Update load totals
            $load->decrement('total_weight_lbs', $item->weight_lbs);
            $load->decrement('total_weight_kg', $item->weight_kg);
            $load->decrement('total_pieces');

            $item->delete();
        });

        return redirect()
            ->back()
            ->with('success', 'Item removed from load successfully.');
    }

    /**
     * Ship the load.
     */
    public function ship(Load $load): RedirectResponse
    {
        if ($load->status !== 'planned') {
            return redirect()
                ->back()
                ->with('error', 'Only planned loads can be shipped.');
        }

        $this->shippingService->shipLoad($load);

        return redirect()
            ->route('shipping.show', $load)
            ->with('success', 'Load shipped successfully.');
    }

    /**
     * Mark the load as delivered.
     */
    public function deliver(Load $load): RedirectResponse
    {
        if ($load->status !== 'in_transit') {
            return redirect()
                ->back()
                ->with('error', 'Only in-transit loads can be marked as delivered.');
        }

        $load->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        // Update all items on the load
        foreach ($load->items as $item) {
            $item->assemblyInstance->update([
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);
        }

        return redirect()
            ->route('shipping.show', $load)
            ->with('success', 'Load marked as delivered successfully.');
    }

    /**
     * Mark load as planned (ready to ship).
     */
    public function plan(Load $load): RedirectResponse
    {
        if ($load->status !== 'draft') {
            return redirect()
                ->back()
                ->with('error', 'Only draft loads can be planned.');
        }

        if ($load->items()->count() === 0) {
            return redirect()
                ->back()
                ->with('error', 'Cannot plan an empty load.');
        }

        $load->update(['status' => 'planned']);

        return redirect()
            ->route('shipping.show', $load)
            ->with('success', 'Load planned successfully.');
    }

    /**
     * Generate a unique load number.
     */
    protected function generateLoadNumber(): string
    {
        $prefix = 'LOAD-'.date('Ymd');
        $lastLoad = Load::where('load_number', 'like', $prefix.'%')
            ->orderByDesc('load_number')
            ->first();

        if ($lastLoad) {
            $lastNumber = (int) substr($lastLoad->load_number, -4);
            $nextNumber = str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return $prefix.'-'.$nextNumber;
    }
}
