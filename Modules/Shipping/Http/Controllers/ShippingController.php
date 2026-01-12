<?php

// app/Http/Controllers/ShippingController.php

namespace Modules\Shipping\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AssemblyInstance;
use App\Models\Load;
use App\Models\Project;
use App\Services\ShippingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShippingController extends Controller
{
    public function __construct(
        protected ShippingService $shippingService,
    ) {}

    /**
     * Display a dashboard view of shipping loads.
     */
    public function index(Request $request): Response
    {
        $sortBy = $request->input('sort_by', 'ship_date');
        $sortDirection = $request->input('sort_direction', 'desc');
        $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'desc';

        $sortableColumns = [
            'load_number' => 'load_number',
            'project' => 'project_id',
            'destination' => 'destination',
            'ship_date' => 'ship_date',
            'status' => 'status',
            'carrier' => 'carrier',
            'pieces' => 'total_pieces',
            'weight' => 'total_weight_lbs',
        ];

        $sortColumn = $sortableColumns[$sortBy] ?? 'ship_date';

        $loads = Load::with('project:id,job_number,name')
            ->withCount(['items as item_count'])
            ->select([
                'id',
                'load_number',
                'project_id',
                'status',
                'destination',
                'ship_date',
                'carrier',
                'truck_number',
                'trailer_number',
                'total_weight_lbs',
                'total_weight_kg',
                'total_pieces',
                'bol_number',
                'shipped_at',
                'delivered_at',
                'notes',
                'created_at',
            ])
            ->orderBy($sortColumn, $sortDirection)
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'totalLoads' => Load::count(),
            'pendingLoads' => Load::where('status', 'pending')->count(),
            'inTransit' => Load::where('status', 'in_transit')->count(),
            'delivered' => Load::where('status', 'delivered')->count(),
            'totalPieces' => (int) Load::sum('total_pieces'),
            'totalWeightLbs' => (float) Load::sum('total_weight_lbs'),
            'nextShipDate' => Load::whereNotNull('ship_date')->orderBy('ship_date')->value('ship_date'),
        ];

        return Inertia::render('Shipping::Index', [
            'loads' => $loads,
            'stats' => $stats,
            'filters' => $request->only(['sort_by', 'sort_direction']),
        ]);
    }

    /**
     * Show the form for creating a new load.
     */
    public function create(): Response
    {
        $projects = Project::orderBy('job_number')->get(['id', 'job_number', 'name']);

        return Inertia::render('Shipping::Create', [
            'projects' => $projects,
            'statuses' => ['pending', 'in_transit', 'delivered', 'cancelled'],
        ]);
    }

    /**
     * Store a newly created load.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'load_number' => 'required|string|max:255',
            'project_id' => 'required|exists:projects,id',
            'destination' => 'nullable|string|max:255',
            'ship_date' => 'nullable|date',
            'carrier' => 'nullable|string|max:255',
            'truck_number' => 'nullable|string|max:255',
            'trailer_number' => 'nullable|string|max:255',
            'driver_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $load = Load::create($validated);

        return redirect()
            ->route('shipping.show', $load)
            ->with('success', 'Load created successfully.');
    }

    /**
     * Display the specified load.
     */
    public function show(Load $load): Response
    {
        $load->load(['project', 'items.assemblyInstance.assembly', 'documents']);

        // Get available assembly instances for this project that haven't been loaded yet
        $availableAssemblies = AssemblyInstance::with('assembly')
            ->where('project_id', $load->project_id)
            ->whereIn('status', ['complete', 'inspected'])
            ->whereNull('load_id')
            ->get();

        return Inertia::render('Shipping::Show', [
            'load' => $load,
            'availableAssemblies' => $availableAssemblies,
        ]);
    }

    /**
     * Add an assembly instance to a load.
     */
    public function addItem(Request $request, Load $load): RedirectResponse
    {
        $validated = $request->validate([
            'assembly_instance_id' => 'required|exists:assembly_instances,id',
        ]);

        try {
            $instance = AssemblyInstance::findOrFail($validated['assembly_instance_id']);

            // Validate the instance can be added
            if ($instance->load_id) {
                return redirect()
                    ->back()
                    ->with('error', 'Assembly is already assigned to a load.');
            }

            if ($instance->project_id !== $load->project_id) {
                return redirect()
                    ->back()
                    ->with('error', 'Assembly must belong to the same project as the load.');
            }

            $this->shippingService->addItemToLoad($load, $instance);

            return redirect()
                ->route('shipping.show', $load)
                ->with('success', 'Assembly added to load successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to add item to load: '.$e->getMessage());
        }
    }

    /**
     * Mark a load as shipped.
     */
    public function ship(Load $load): RedirectResponse
    {
        if ($load->status === 'in_transit' || $load->status === 'delivered') {
            return redirect()
                ->back()
                ->with('error', 'Load has already been shipped.');
        }

        if ($load->items()->count() === 0) {
            return redirect()
                ->back()
                ->with('error', 'Cannot ship an empty load.');
        }

        try {
            $this->shippingService->shipLoad($load);

            return redirect()
                ->route('shipping.show', $load)
                ->with('success', 'Load marked as shipped successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to ship load: '.$e->getMessage());
        }
    }
}
