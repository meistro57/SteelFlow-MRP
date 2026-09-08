<?php

namespace Modules\DrawingFlow\Http\Controllers;

use App\Http\Requests\ProcessSubmittalApprovalRequest;
use App\Http\Requests\UpdateSubmittalPurposeRequest;
use App\Models\DrawingRequest;
use App\Models\DrawingSubmittal;
use App\Models\User;
use Modules\DrawingFlow\Notifications\SubmittalApprovalRecorded;
use App\Services\FabHandoffService;
use App\Services\SubmittalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SubmittalController extends Controller
{
    public function __construct(
        private SubmittalService $service,
        private FabHandoffService $fabService,
    ) {}

    public function index(): Response
    {
        $submittals = DrawingSubmittal::with(['project', 'customer', 'submittedBy', 'drawingRequest'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Submittals/Index', [
            'submittals' => $submittals,
        ]);
    }

    public function show(DrawingSubmittal $submittal): Response
    {
        $submittal->load([
            'project',
            'customer',
            'submittedBy',
            'drawingRequest',
            'files' => fn ($q) => $q->with('uploadedBy:id,name')->latest(),
            'approvals' => fn ($q) => $q->with('createdBy')->latest(),
            'submittalNotes' => fn ($q) => $q->with('user:id,name')->latest(),
            'fabQueueEntry.assignedTo',
        ]);

        return Inertia::render('Submittals/Show', [
            'submittal' => $submittal,
            'purposeOptions' => DrawingSubmittal::PURPOSE_OPTIONS,
        ]);
    }

    public function updatePurpose(UpdateSubmittalPurposeRequest $request, DrawingSubmittal $submittal): RedirectResponse
    {
        $purpose = $request->validated('purpose');

        if (DB::getDriverName() === 'sqlite') {
            $purpose = match ($purpose) {
                'for_fab' => 'for_construction',
                'for_material_order', 'for_pricing', 'for_field_verification', 'preliminary' => 'for_information',
                default => $purpose,
            };
        }

        $submittal->update([
            'purpose' => $purpose,
        ]);

        return back()->with('success', 'Submittal purpose updated successfully.');
    }

    public function createFromRequest(DrawingRequest $drawingRequest): RedirectResponse
    {
        $submittal = $this->service->createFromRequest($drawingRequest, auth()->id());

        return redirect()->route('submittals.show', $submittal)
            ->with('success', 'Submittal created from drawing request.');
    }

    public function submit(DrawingSubmittal $submittal): RedirectResponse
    {
        $this->service->submit($submittal);

        return back()->with('success', 'Submittal has been submitted for approval.');
    }

    public function processApproval(ProcessSubmittalApprovalRequest $request, DrawingSubmittal $submittal): RedirectResponse
    {
        $validated = $request->validated();
        $validated['created_by_user_id'] = auth()->id();

        $this->service->processApproval($submittal, $validated['approval_type'], $validated);

        $submittal->refresh()->loadMissing(['drawingRequest.assignedTo', 'submittedBy']);

        // Auto-create fab queue entry if approved
        if (in_array($validated['approval_type'], ['approved', 'approved_as_noted'])) {
            $this->fabService->createFabQueueEntry($submittal);
        }

        $recipientIds = collect([
            $submittal->submitted_by_user_id,
            $submittal->drawingRequest?->assigned_to_user_id,
        ])
            ->filter()
            ->unique()
            ->reject(fn ($userId) => (int) $userId === (int) auth()->id())
            ->values();

        if ($recipientIds->isNotEmpty()) {
            $recipients = User::query()->whereIn('id', $recipientIds)->get();

            foreach ($recipients as $recipient) {
                $recipient->notify(new SubmittalApprovalRecorded(
                    $submittal,
                    $validated['approval_type'],
                    auth()->user()->name,
                ));
            }
        }

        return back()->with('success', 'Approval processed successfully.');
    }

    public function createRevision(DrawingSubmittal $submittal): RedirectResponse
    {
        $newSubmittal = $this->service->createRevision($submittal);

        return redirect()->route('submittals.show', $newSubmittal)
            ->with('success', "Revision {$newSubmittal->revision} created.");
    }

    public function destroy(DrawingSubmittal $submittal): RedirectResponse
    {
        $submittal->delete();

        return redirect()->route('submittals.index')
            ->with('success', 'Submittal deleted successfully.');
    }
}
