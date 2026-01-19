<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportKissRequest;
use App\Http\Requests\ImportXsrRequest;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Customer;
use App\Models\Project;
use App\Services\Import\KissImporter;
use App\Services\Import\XsrImporter;
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
            'bid' => 'Bid',
            'active' => 'Active',
            'production' => 'Production',
            'shipping' => 'Shipping',
            'complete' => 'Complete',
            'archived' => 'Archived',
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

    /**
     * Show the KISS import form for a project.
     */
    public function importKissForm(Project $project): Response
    {
        return Inertia::render('Projects/ImportKiss', [
            'project' => $project,
        ]);
    }

    /**
     * Import KISS file for a project.
     */
    public function importKiss(ImportKissRequest $request, Project $project, KissImporter $importer): RedirectResponse
    {
        $file = $request->file('file');
        $tempPath = $file->getRealPath();

        $success = $importer->import($tempPath, $project);

        if ($success) {
            return redirect()
                ->route('projects.show', $project)
                ->with('success', 'KISS file imported successfully.');
        }

        return redirect()
            ->back()
            ->withErrors(['file' => 'Failed to import KISS file. Please check the file format.'])
            ->withInput();
    }

    /**
     * Show the XSR import form for a project.
     */
    public function importXsrForm(Project $project): Response
    {
        return Inertia::render('Projects/ImportXsr', [
            'project' => $project,
        ]);
    }

    /**
     * Import XSR file for a project.
     */
    public function importXsr(ImportXsrRequest $request, Project $project, XsrImporter $importer): RedirectResponse
    {
        $file = $request->file('file');
        $tempPath = $file->getRealPath();

        $success = $importer->import($tempPath, $project);

        if ($success) {
            return redirect()
                ->route('projects.show', $project)
                ->with('success', 'XSR file imported successfully.');
        }

        return redirect()
            ->back()
            ->withErrors(['file' => 'Failed to import XSR file. Please check the file format.'])
            ->withInput();
    }
}
