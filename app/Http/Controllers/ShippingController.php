<?php

// app/Http/Controllers/ShippingController.php

namespace App\Http\Controllers;

use App\Models\Load;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShippingController extends Controller
{
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

        return Inertia::render('Shipping/Index', [
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

        return Inertia::render('Shipping/Create', [
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

        return Inertia::render('Shipping/Show', [
            'load' => $load,
        ]);
    }
}
