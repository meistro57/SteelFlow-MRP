<?php

namespace Modules\DrawingFlow\Http\Controllers;

use Modules\DrawingFlow\Http\Requests\FabQueueAssignRequest;
use Modules\DrawingFlow\Http\Requests\FabQueueUpdateNotesRequest;
use App\Models\FabQueue;
use App\Models\User;
use App\Services\FabHandoffService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class FabQueueController extends Controller
{
    public function __construct(
        private FabHandoffService $service
    ) {}

    public function index(): Response
    {
        $entries = FabQueue::with(['submittal.drawingRequest', 'project', 'assignedTo'])
            ->orderBy('priority')
            ->orderBy('created_at')
            ->paginate(15);

        return Inertia::render('FabQueue/Index', [
            'entries' => $entries,
            'users' => User::where('active', true)->orderBy('name')->get(['id', 'name', 'role']),
        ]);
    }

    public function show(FabQueue $fabQueue): Response
    {
        $fabQueue->load([
            'submittal.drawingRequest',
            'submittal.customer',
            'submittal.files',
            'project.attachments' => fn ($q) => $q->where('is_latest', true)->orderBy('category')->orderBy('original_filename'),
            'assignedTo',
        ]);

        $submittalFiles = collect();
        if ($fabQueue->submittal) {
            $submittalFiles = $fabQueue->submittal->files->map(fn ($file) => [
                'id' => $file->id,
                'filename' => $file->original_filename,
                'mime_type' => $file->mime_type,
                'file_size' => $file->file_size_formatted,
                'file_type' => $file->file_type,
                'uploaded_at' => $file->uploaded_at?->format('M j, Y'),
                'view_url' => route('submittals.files.view', [$fabQueue->submittal_id, $file->id]),
                'download_url' => route('submittals.files.download', [$fabQueue->submittal_id, $file->id]),
                'source' => 'submittal',
            ]);
        }

        $projectFiles = collect();
        if ($fabQueue->project) {
            $projectFiles = $fabQueue->project->attachments->map(fn ($file) => [
                'id' => $file->id,
                'filename' => $file->original_filename,
                'mime_type' => $file->mime_type,
                'file_size' => null,
                'category' => $file->category,
                'uploaded_at' => $file->created_at?->format('M j, Y'),
                'view_url' => route('projects.attachments.view', [$fabQueue->project_id, $file->id]),
                'download_url' => route('projects.attachments.download', [$fabQueue->project_id, $file->id]),
                'source' => 'project',
            ]);
        }

        return Inertia::render('FabQueue/Show', [
            'entry' => $fabQueue,
            'users' => User::where('active', true)->orderBy('name')->get(['id', 'name', 'role']),
            'submittalFiles' => $submittalFiles->values(),
            'projectFiles' => $projectFiles->values(),
        ]);
    }

    public function assign(FabQueueAssignRequest $request, FabQueue $fabQueue): RedirectResponse
    {
        $user = User::findOrFail($request->validated('user_id'));
        $this->service->assignToFabricator($fabQueue, $user);

        return back()->with('success', "Assigned to {$user->name}.");
    }

    public function complete(FabQueue $fabQueue): RedirectResponse
    {
        $this->service->complete($fabQueue);

        return back()->with('success', 'Fab queue entry marked as completed.');
    }

    public function updateNotes(FabQueueUpdateNotesRequest $request, FabQueue $fabQueue): RedirectResponse
    {
        $fabQueue->update($request->validated());

        return back()->with('success', 'Notes updated.');
    }
}
