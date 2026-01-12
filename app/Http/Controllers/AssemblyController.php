<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAssemblyRequest;
use App\Http\Requests\UpdateAssemblyRequest;
use App\Models\Assembly;
use App\Models\Project;
use App\Services\BOMExtensionService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AssemblyController extends Controller
{
    public function __construct(
        protected BOMExtensionService $bomService,
    ) {}

    /**
     * Show the form for creating a new assembly.
     */
    public function create(Project $project): Response
    {
        return Inertia::render('Assemblies/Create', [
            'project' => $project,
        ]);
    }

    /**
     * Store a newly created assembly.
     */
    public function store(StoreAssemblyRequest $request): RedirectResponse
    {
        $assembly = Assembly::create($request->validated());

        // Recalculate weights and sync instances
        $this->bomService->extendAssembly($assembly);

        return redirect()
            ->route('projects.show', $assembly->project_id)
            ->with('success', 'Assembly created successfully.');
    }

    /**
     * Display the specified assembly.
     */
    public function show(Assembly $assembly): Response
    {
        $assembly->load(['project', 'parts.material', 'drawing', 'instances']);

        return Inertia::render('Assemblies/Show', [
            'assembly' => $assembly,
        ]);
    }

    /**
     * Show the form for editing the specified assembly.
     */
    public function edit(Assembly $assembly): Response
    {
        $assembly->load('project');

        return Inertia::render('Assemblies/Edit', [
            'assembly' => $assembly,
        ]);
    }

    /**
     * Update the specified assembly.
     */
    public function update(UpdateAssemblyRequest $request, Assembly $assembly): RedirectResponse
    {
        $assembly->update($request->validated());

        // Recalculate weights and sync instances
        $this->bomService->extendAssembly($assembly);

        return redirect()
            ->route('projects.show', $assembly->project_id)
            ->with('success', 'Assembly updated successfully.');
    }

    /**
     * Remove the specified assembly.
     */
    public function destroy(Assembly $assembly): RedirectResponse
    {
        $projectId = $assembly->project_id;
        $assembly->delete();

        return redirect()
            ->route('projects.show', $projectId)
            ->with('success', 'Assembly deleted successfully.');
    }
}
