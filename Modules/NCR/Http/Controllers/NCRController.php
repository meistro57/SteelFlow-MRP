<?php

namespace Modules\NCR\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PartInstance;
use App\Models\ProductionItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Inventory\Models\StockItem;
use Modules\NCR\Models\NCR;
use Modules\NCR\Services\NCRService;

class NCRController extends Controller
{
    public function __construct(
        protected NCRService $ncrService
    ) {}

    /**
     * Display a listing of NCRs.
     */
    public function index(Request $request): Response
    {
        $query = NCR::with([
            'reporter',
            'productionItem',
            'partInstance.part',
            'stockItem',
        ]);

        // Apply status filter
        if ($request->filled('status')) {
            $query->whereState('status', $request->status);
        }

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhere('failure_reason', 'like', "%{$search}%");
            });
        }

        // Calculate summary stats
        $stats = [
            'total' => NCR::count(),
            'open' => NCR::whereState('status', 'open')->count(),
            'under_review' => NCR::whereState('status', 'under_review')->count(),
            'dispositioned' => NCR::whereState('status', 'dispositioned')->count(),
            'closed' => NCR::whereState('status', 'closed')->count(),
        ];

        $ncrs = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('NCR::Index', [
            'ncrs' => $ncrs,
            'stats' => $stats,
            'filters' => $request->only(['status', 'search']),
            'statuses' => $this->getStatuses(),
        ]);
    }

    /**
     * Show the form for creating a new NCR.
     */
    public function create(Request $request): Response
    {
        $partInstances = [];
        $productionItems = [];
        $stockItems = [];

        if ($request->filled('type')) {
            switch ($request->type) {
                case 'part':
                    $partInstances = PartInstance::with('part.assembly')
                        ->whereIn('status', ['in_progress', 'complete'])
                        ->get();
                    break;
                case 'production':
                    $productionItems = ProductionItem::with('partInstance.part')
                        ->whereIn('status', ['in_progress'])
                        ->get();
                    break;
                case 'material':
                    $stockItems = StockItem::with('material')
                        ->whereIn('status', ['free', 'assigned', 'committed'])
                        ->get();
                    break;
            }
        }

        return Inertia::render('NCR::Create', [
            'partInstances' => $partInstances,
            'productionItems' => $productionItems,
            'stockItems' => $stockItems,
            'selectedType' => $request->type,
        ]);
    }

    /**
     * Store a newly created NCR.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:part,production,material',
            'part_instance_id' => 'nullable|exists:part_instances,id',
            'production_item_id' => 'nullable|exists:production_items,id',
            'stock_item_id' => 'nullable|exists:stock_items,id',
            'failure_reason' => 'required|string',
            'remediation_notes' => 'nullable|string',
        ]);

        $ncr = NCR::create([
            'number' => $this->generateNCRNumber(),
            'part_instance_id' => $validated['part_instance_id'] ?? null,
            'production_item_id' => $validated['production_item_id'] ?? null,
            'stock_item_id' => $validated['stock_item_id'] ?? null,
            'failure_reason' => $validated['failure_reason'],
            'remediation_notes' => $validated['remediation_notes'] ?? null,
            'reported_by' => auth()->id(),
            'status' => 'open',
        ]);

        return redirect()
            ->route('ncr.show', $ncr)
            ->with('success', 'NCR created successfully.');
    }

    /**
     * Display the specified NCR.
     */
    public function show(NCR $ncr): Response
    {
        $ncr->load([
            'reporter',
            'dispositioner',
            'productionItem.partInstance.part',
            'partInstance.part.assembly',
            'stockItem.material',
        ]);

        return Inertia::render('NCR::Show', [
            'ncr' => $ncr,
            'dispositions' => $this->getDispositions(),
        ]);
    }

    /**
     * Disposition an NCR.
     */
    public function disposition(Request $request, NCR $ncr): RedirectResponse
    {
        $validated = $request->validate([
            'disposition' => 'required|in:SCRAP,REWORK,USE_AS_IS',
            'notes' => 'nullable|string',
            'rework_operation' => 'nullable|string',
        ]);

        try {
            $this->ncrService->disposition($ncr, $validated['disposition'], [
                'notes' => $validated['notes'] ?? null,
                'rework_operation' => $validated['rework_operation'] ?? null,
            ]);

            return redirect()
                ->route('ncr.show', $ncr)
                ->with('success', 'NCR dispositioned successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to disposition NCR: '.$e->getMessage());
        }
    }

    /**
     * Close an NCR.
     */
    public function close(NCR $ncr): RedirectResponse
    {
        try {
            $this->ncrService->close($ncr);

            return redirect()
                ->route('ncr.show', $ncr)
                ->with('success', 'NCR closed successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to close NCR: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified NCR.
     */
    public function destroy(NCR $ncr): RedirectResponse
    {
        if (! $ncr->status->canTransitionTo('open')) {
            return redirect()
                ->back()
                ->with('error', 'Cannot delete NCRs that have been dispositioned.');
        }

        $ncr->delete();

        return redirect()
            ->route('ncr.index')
            ->with('success', 'NCR deleted successfully.');
    }

    /**
     * Generate unique NCR number.
     */
    protected function generateNCRNumber(): string
    {
        $year = now()->format('Y');
        $lastNCR = NCR::where('number', 'like', "NCR-{$year}-%")->latest()->first();

        if ($lastNCR) {
            $lastNumber = (int) substr($lastNCR->number, -4);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return "NCR-{$year}-{$nextNumber}";
    }

    /**
     * Get available statuses.
     */
    protected function getStatuses(): array
    {
        return [
            'open' => 'Open',
            'under_review' => 'Under Review',
            'dispositioned' => 'Dispositioned',
            'closed' => 'Closed',
        ];
    }

    /**
     * Get available dispositions.
     */
    protected function getDispositions(): array
    {
        return [
            'SCRAP' => 'Scrap',
            'REWORK' => 'Rework',
            'USE_AS_IS' => 'Use As-Is',
        ];
    }
}
