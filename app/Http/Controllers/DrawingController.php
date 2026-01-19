<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDrawingRequest;
use App\Http\Requests\UpdateDrawingRequest;
use App\Http\Requests\UploadDrawingRequest;
use App\Models\Drawing;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class DrawingController extends Controller
{
    /**
     * Display a paginated list of drawings with filters.
     */
    public function index(): Response
    {
        $drawings = Drawing::query()
            ->with('project')
            ->when(request('search'), function ($query, $search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('number', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhere('revision', 'like', "%{$search}%");
                });
            })
            ->when(request('project_id'), fn ($q, $projectId) => $q->where('project_id', $projectId))
            ->when(request()->filled('has_file'), function ($query): void {
                $hasFile = filter_var(request('has_file'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

                if ($hasFile === true) {
                    $query->whereNotNull('file_path');
                } elseif ($hasFile === false) {
                    $query->whereNull('file_path');
                }
            })
            ->orderByDesc('revised_at')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Drawings/Index', [
            'drawings' => $drawings,
            'projects' => Project::orderBy('name')->get(['id', 'name', 'job_number']),
            'filters' => request()->only(['search', 'project_id', 'has_file']),
        ]);
    }

    /**
     * Show the form for creating a new drawing.
     */
    public function create(): Response
    {
        return Inertia::render('Drawings/Create', [
            'projects' => Project::orderBy('name')->get(['id', 'name', 'job_number']),
        ]);
    }

    /**
     * Store a newly created drawing.
     */
    public function store(StoreDrawingRequest $request): RedirectResponse
    {
        $drawing = Drawing::create($request->validated());

        return redirect()
            ->route('drawings.edit', $drawing)
            ->with('success', 'Drawing created successfully.');
    }

    /**
     * Show the form for editing the specified drawing.
     */
    public function edit(Drawing $drawing): Response
    {
        $drawing->load('project');

        return Inertia::render('Drawings/Edit', [
            'drawing' => $drawing,
            'projects' => Project::orderBy('name')->get(['id', 'name', 'job_number']),
        ]);
    }

    /**
     * Update the specified drawing.
     */
    public function update(UpdateDrawingRequest $request, Drawing $drawing): RedirectResponse
    {
        $drawing->update($request->validated());

        return redirect()
            ->route('drawings.index')
            ->with('success', 'Drawing updated successfully.');
    }

    /**
     * Remove the specified drawing.
     */
    public function destroy(Drawing $drawing): RedirectResponse
    {
        if ($drawing->file_path && Storage::disk('drawings')->exists($drawing->file_path)) {
            Storage::disk('drawings')->delete($drawing->file_path);
        }

        $drawing->delete();

        return redirect()
            ->route('drawings.index')
            ->with('success', 'Drawing deleted successfully.');
    }

    public function show(Drawing $drawing)
    {
        if (! Storage::disk('drawings')->exists($drawing->file_path)) {
            abort(404);
        }

        return Storage::disk('drawings')->response($drawing->file_path);
    }

    public function upload(UploadDrawingRequest $request, Drawing $drawing)
    {
        if ($drawing->file_path && Storage::disk('drawings')->exists($drawing->file_path)) {
            Storage::disk('drawings')->delete($drawing->file_path);
        }

        $file = $request->file('file');
        $safeName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $extension = $file->getClientOriginalExtension();

        $path = $file->storeAs(
            'project_'.$drawing->project_id,
            now()->format('Ymd_His').'_'.$safeName.'.'.$extension,
            'drawings',
        );

        $drawing->update([
            'file_path' => $path,
            'revision' => $request->string('revision')->trim()->isNotEmpty()
                ? $request->string('revision')->trim()
                : $drawing->revision,
            'revised_at' => now(),
        ]);

        return back()->with('success', 'Drawing uploaded successfully');
    }
}
