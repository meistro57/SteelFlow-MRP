<?php

namespace Modules\DrawingFlow\Http\Controllers;

use App\Http\Requests\AssignDrawingRequestRequest;
use Modules\DrawingFlow\Http\Requests\DrawingRequestFormRequest;
use App\Models\Customer;
use App\Models\DrawingRequest;
use App\Models\Project;
use App\Models\User;
use Modules\DrawingFlow\Services\DrawingRequestService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class DrawingRequestController extends Controller
{
    public function __construct(
        private DrawingRequestService $service
    ) {}

    public function index(): Response
    {
        $requests = DrawingRequest::with(['project', 'customer', 'assignedTo'])
            ->latest()
            ->paginate(15);

        return Inertia::render('DrawingRequests/Index', [
            'requests' => $requests,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('DrawingRequests/Create', [
            'customers' => $this->customersForForm(),
            'projects' => $this->projectsForForm(),
            'users' => User::where('active', true)->orderBy('name')->get(['id', 'name', 'role']),
            'drawingTypes' => DrawingRequestFormRequest::DRAWING_TYPES,
            'preselected_project_id' => request('project_id'),
        ]);
    }

    public function store(DrawingRequestFormRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['requested_by_user_id'] = auth()->id();

        $drawingRequest = $this->service->create($data);

        return redirect()->route('drawing-requests.show', $drawingRequest)
            ->with('success', 'Drawing request created successfully.');
    }

    public function show(DrawingRequest $drawingRequest): Response
    {
        $drawingRequest->load([
            'project',
            'customer',
            'assignedTo',
            'requestedBy',
            'submittals' => fn ($q) => $q->with('submittedBy')->latest(),
        ]);

        return Inertia::render('DrawingRequests/Show', [
            'drawingRequest' => $drawingRequest,
            'users' => User::where('active', true)->orderBy('name')->get(['id', 'name', 'role']),
        ]);
    }

    public function edit(DrawingRequest $drawingRequest): Response
    {
        return Inertia::render('DrawingRequests/Edit', [
            'drawingRequest' => $drawingRequest,
            'customers' => $this->customersForForm(),
            'projects' => $this->projectsForForm(),
            'users' => User::where('active', true)->orderBy('name')->get(['id', 'name', 'role']),
            'drawingTypes' => DrawingRequestFormRequest::DRAWING_TYPES,
        ]);
    }

    public function update(DrawingRequestFormRequest $request, DrawingRequest $drawingRequest): RedirectResponse
    {
        $drawingRequest->update($request->validated());

        return redirect()->route('drawing-requests.show', $drawingRequest)
            ->with('success', 'Drawing request updated successfully.');
    }

    public function destroy(DrawingRequest $drawingRequest): RedirectResponse
    {
        $drawingRequest->delete();

        return redirect()->route('drawing-requests.index')
            ->with('success', 'Drawing request deleted successfully.');
    }

    public function assign(AssignDrawingRequestRequest $request, DrawingRequest $drawingRequest): RedirectResponse
    {
        $this->service->assign($drawingRequest, (int) $request->validated('user_id'));

        return back()->with('success', 'Drawing request assigned successfully.');
    }

    public function markReady(DrawingRequest $drawingRequest): RedirectResponse
    {
        $this->service->markReadyToSubmit($drawingRequest);

        return back()->with('success', 'Drawing request marked as ready to submit.');
    }

    public function cancel(DrawingRequest $drawingRequest): RedirectResponse
    {
        $this->service->cancel($drawingRequest);

        return back()->with('success', 'Drawing request cancelled.');
    }

    public function hold(DrawingRequest $drawingRequest): RedirectResponse
    {
        $this->service->putOnHold($drawingRequest);

        return back()->with('success', 'Drawing request put on hold.');
    }

    private function customersForForm()
    {
        return Customer::active()
            ->orderBy('name')
            ->get(['id', 'name', 'address', 'city', 'state', 'zip', 'country']);
    }

    private function projectsForForm()
    {
        return Project::active()
            ->with('customer:id,name,address,city,state,zip,country')
            ->orderBy('name')
            ->get(['id', 'name', 'project_number', 'customer_id', 'address', 'city', 'state', 'zip']);
    }
}
