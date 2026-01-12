<?php

namespace App\Http\Controllers;

use App\Models\PartInstance;
use App\Models\PartWorkArea;
use App\Models\WorkArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoutingController extends Controller
{
    public function index(): Response
    {
        $workAreas = WorkArea::with('department')->get();

        return Inertia::render('Production/Routing', [
            'workAreas' => $workAreas,
        ]);
    }

    public function create(PartInstance $partInstance): Response
    {
        return Inertia::render('Production/Routing/Create', [
            'partInstance' => $partInstance->load(['part', 'project']),
            'workAreas' => WorkArea::all(),
        ]);
    }

    public function store(Request $request, PartInstance $partInstance): RedirectResponse
    {
        $validated = $request->validate([
            'work_area_id' => 'required|exists:work_areas,id',
            'sequence_number' => 'required|integer|min:1',
            'estimated_hours' => 'nullable|numeric|min:0',
        ]);

        $partInstance->routing()->create($validated + ['status' => 'pending']);

        return back()->with('success', 'Routing step added.');
    }

    public function updateStatus(Request $request, PartWorkArea $step): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,complete,on_hold',
        ]);

        $step->update($validated);

        return back()->with('success', 'Step status updated.');
    }
}
