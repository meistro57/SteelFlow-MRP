<?php

namespace Modules\Nesting\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Nesting;
use App\Models\Project;
use App\Services\Nesting\NestingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NestingController extends Controller
{
    public function __construct(
        protected NestingService $nestingService,
    ) {}

    public function index(): Response
    {
        $nestings = Nesting::with(['project'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Nesting::Index', [
            'nestings' => $nestings,
        ]);
    }

    public function show(Nesting $nesting): Response
    {
        $nesting->load(['project', 'bars.nestingParts.partInstance.part', 'bars.stockItem']);

        return Inertia::render('Nesting::Show', [
            'nesting' => $nesting,
        ]);
    }

    public function create(Project $project): Response
    {
        return Inertia::render('Nesting::Create', [
            'project' => $project,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'type' => 'required|string|in:linear,plate',
            'kerf_allowance' => 'required|numeric|min:0',
        ]);

        try {
            $nesting = $this->nestingService->createNesting($validated);

            return redirect()
                ->route('nesting.show', $nesting)
                ->with('success', 'Nesting optimizer completed successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to run optimizer: '.$e->getMessage());
        }
    }

    /**
     * Approve a nesting (assign stock to nesting).
     */
    public function approve(Nesting $nesting): RedirectResponse
    {
        if ($nesting->status !== 'draft') {
            return redirect()
                ->back()
                ->with('error', 'Only draft nestings can be approved.');
        }

        try {
            $this->nestingService->approve($nesting);

            return redirect()
                ->route('nesting.show', $nesting)
                ->with('success', 'Nesting approved successfully. Stock items have been assigned.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to approve nesting: '.$e->getMessage());
        }
    }

    /**
     * Confirm a nesting (mark stock as used and create remnants).
     */
    public function confirm(Nesting $nesting): RedirectResponse
    {
        if ($nesting->status !== 'approved') {
            return redirect()
                ->back()
                ->with('error', 'Only approved nestings can be confirmed.');
        }

        try {
            $this->nestingService->confirm($nesting);

            return redirect()
                ->route('nesting.show', $nesting)
                ->with('success', 'Nesting confirmed successfully. Stock items marked as used and remnants created.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to confirm nesting: '.$e->getMessage());
        }
    }
}
