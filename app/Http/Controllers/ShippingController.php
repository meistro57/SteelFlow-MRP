<?php

// app/Http/Controllers/ShippingController.php

namespace App\Http\Controllers;

use App\Models\Load;
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
}
