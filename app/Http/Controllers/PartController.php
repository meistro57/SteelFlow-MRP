<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePartRequest;
use App\Http\Requests\UpdatePartRequest;
use App\Models\Assembly;
use App\Models\Part;
use App\Services\BOMExtensionService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PartController extends Controller
{
    public function __construct(
        protected BOMExtensionService $bomService,
    ) {}

    /**
     * Show the form for creating a new part.
     */
    public function create(Assembly $assembly): Response
    {
        return Inertia::render('Parts/Create', [
            'assembly' => $assembly->load('project'),
        ]);
    }

    /**
     * Store a newly created part.
     */
    public function store(StorePartRequest $request): RedirectResponse
    {
        $part = Part::create($request->validated());

        // Recalculate assembly weights and sync instances
        $this->bomService->extendAssembly($part->assembly);

        return redirect()
            ->route('assemblies.show', $part->assembly_id)
            ->with('success', 'Part created successfully.');
    }

    /**
     * Display the specified part.
     */
    public function show(Part $part): Response
    {
        $part->load(['assembly.project', 'material', 'instances']);

        return Inertia::render('Parts/Show', [
            'part' => $part,
        ]);
    }

    /**
     * Show the form for editing the specified part.
     */
    public function edit(Part $part): Response
    {
        $part->load('assembly.project');

        return Inertia::render('Parts/Edit', [
            'part' => $part,
        ]);
    }

    /**
     * Update the specified part.
     */
    public function update(UpdatePartRequest $request, Part $part): RedirectResponse
    {
        $part->update($request->validated());

        // Recalculate assembly weights and sync instances
        $this->bomService->extendAssembly($part->assembly);

        return redirect()
            ->route('assemblies.show', $part->assembly_id)
            ->with('success', 'Part updated successfully.');
    }

    /**
     * Remove the specified part.
     */
    public function destroy(Part $part): RedirectResponse
    {
        $assemblyId = $part->assembly_id;
        $assembly = $part->assembly;
        $part->delete();

        // Recalculate after deletion
        $this->bomService->extendAssembly($assembly);

        return redirect()
            ->route('assemblies.show', $assemblyId)
            ->with('success', 'Part deleted successfully.');
    }
}
