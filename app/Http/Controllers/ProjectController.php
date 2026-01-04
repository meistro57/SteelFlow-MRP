<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Customer;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects.
     */
    public function index(): Response
    {
        $projects = Project::with('customer')
            ->withCount(['assemblies', 'parts'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'filters' => request()->only(['search', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new project.
     */
    public function create(): Response
    {
        $customers = Customer::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Projects/Create', [
            'customers' => $customers,
            'statuses' => $this->getStatuses(),
            'jobTypes' => $this->getJobTypes(),
        ]);
    }

    /**
     * Store a newly created project.
     */
    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $project = Project::create($request->validated());

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project): Response
    {
        $project->load([
            'customer',
            'assemblies' => fn ($q) => $q->withCount('parts'),
            'phases',
            'lots',
        ]);

        return Inertia::render('Projects/Show', [
            'project' => $project,
        ]);
    }

    /**
     * Show the form for editing the specified project.
     */
    public function edit(Project $project): Response
    {
        $customers = Customer::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Projects/Edit', [
            'project' => $project,
            'customers' => $customers,
            'statuses' => $this->getStatuses(),
            'jobTypes' => $this->getJobTypes(),
        ]);
    }

    /**
     * Update the specified project.
     */
    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update($request->validated());

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified project.
     */
    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    /**
     * Get available project statuses.
     */
    protected function getStatuses(): array
    {
        return [
            'pending' => 'Pending',
            'active' => 'Active',
            'on_hold' => 'On Hold',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
    }

    /**
     * Get available job types.
     */
    protected function getJobTypes(): array
    {
        return [
            'new_construction' => 'New Construction',
            'renovation' => 'Renovation',
            'repair' => 'Repair',
            'miscellaneous' => 'Miscellaneous',
        ];
    }
}
