<?php

namespace Modules\ShopTicket\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Project;
use App\Models\WorkArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Modules\ShopTicket\Models\ShopTicket;

class ShopTicketController extends Controller
{
    /**
     * Display a listing of shop tickets.
     */
    public function index(Request $request): Response
    {
        $query = ShopTicket::with(['project', 'assignedEmployee', 'steps.workArea'])
            ->orderByDesc('created_at');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Search by ticket number
        if ($request->filled('search')) {
            $query->where('ticket_number', 'like', '%'.$request->search.'%');
        }

        $tickets = $query->paginate(20)->withQueryString();

        // Stats for dashboard
        $stats = [
            'total' => ShopTicket::count(),
            'open' => ShopTicket::where('status', 'open')->count(),
            'in_progress' => ShopTicket::where('status', 'in_progress')->count(),
            'completed' => ShopTicket::where('status', 'completed')->count(),
            'overdue' => ShopTicket::where('status', '!=', 'completed')
                ->whereNotNull('due_date')
                ->where('due_date', '<', now())
                ->count(),
        ];

        return Inertia::render('ShopTicket/Index', [
            'tickets' => $tickets,
            'stats' => $stats,
            'filters' => $request->only(['status', 'priority', 'project_id', 'search']),
            'projects' => Project::orderBy('job_number')->get(['id', 'job_number', 'name']),
        ]);
    }

    /**
     * Show the form for creating a new shop ticket.
     */
    public function create(): Response
    {
        return Inertia::render('ShopTicket/Create', [
            'projects' => Project::with('assemblies.instances')
                ->whereIn('status', ['active', 'production'])
                ->orderBy('job_number')
                ->get(),
            'employees' => Employee::where('is_active', true)->orderBy('name')->get(),
            'workAreas' => WorkArea::with('department')->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created shop ticket in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'assembly_instance_id' => 'nullable|exists:assembly_instances,id',
            'part_instance_id' => 'nullable|exists:part_instances,id',
            'priority' => 'required|integer|min:1|max:5',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
            'assigned_to_employee_id' => 'nullable|exists:employees,id',
            'steps' => 'nullable|array',
            'steps.*.work_area_id' => 'required_with:steps|exists:work_areas,id',
            'steps.*.estimated_hours' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $ticket = ShopTicket::create([
                'ticket_number' => $this->generateTicketNumber(),
                'project_id' => $validated['project_id'],
                'assembly_instance_id' => $validated['assembly_instance_id'] ?? null,
                'part_instance_id' => $validated['part_instance_id'] ?? null,
                'status' => 'open',
                'priority' => $validated['priority'],
                'due_date' => $validated['due_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
                'assigned_to_employee_id' => $validated['assigned_to_employee_id'] ?? null,
            ]);

            // Create steps if provided
            if (! empty($validated['steps'])) {
                foreach ($validated['steps'] as $index => $stepData) {
                    $ticket->steps()->create([
                        'work_area_id' => $stepData['work_area_id'],
                        'sequence' => $index + 1,
                        'status' => 'pending',
                        'estimated_hours' => $stepData['estimated_hours'] ?? 0,
                        'actual_hours' => 0,
                    ]);
                }
            }
        });

        return redirect()
            ->route('shop-tickets.index')
            ->with('success', 'Shop ticket created successfully.');
    }

    /**
     * Display the specified shop ticket.
     */
    public function show(ShopTicket $shopTicket): Response
    {
        $shopTicket->load([
            'project',
            'assemblyInstance.assembly',
            'partInstance.part',
            'creator',
            'assignedEmployee',
            'steps.workArea.department',
            'steps.completer',
        ]);

        return Inertia::render('ShopTicket/Show', [
            'ticket' => $shopTicket,
            'employees' => Employee::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form for editing the specified shop ticket.
     */
    public function edit(ShopTicket $shopTicket): Response
    {
        $shopTicket->load('steps');

        return Inertia::render('ShopTicket/Edit', [
            'ticket' => $shopTicket,
            'projects' => Project::with('assemblies.instances')
                ->orderBy('job_number')
                ->get(),
            'employees' => Employee::where('is_active', true)->orderBy('name')->get(),
            'workAreas' => WorkArea::with('department')->orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified shop ticket in storage.
     */
    public function update(Request $request, ShopTicket $shopTicket): RedirectResponse
    {
        $validated = $request->validate([
            'priority' => 'required|integer|min:1|max:5',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
            'assigned_to_employee_id' => 'nullable|exists:employees,id',
        ]);

        $shopTicket->update($validated);

        return redirect()
            ->route('shop-tickets.show', $shopTicket)
            ->with('success', 'Shop ticket updated successfully.');
    }

    /**
     * Remove the specified shop ticket from storage.
     */
    public function destroy(ShopTicket $shopTicket): RedirectResponse
    {
        $shopTicket->delete();

        return redirect()
            ->route('shop-tickets.index')
            ->with('success', 'Shop ticket deleted successfully.');
    }

    /**
     * Release a shop ticket to production.
     */
    public function release(ShopTicket $shopTicket): RedirectResponse
    {
        if ($shopTicket->status !== 'open') {
            return redirect()
                ->back()
                ->with('error', 'Only open tickets can be released.');
        }

        $shopTicket->update([
            'status' => 'in_progress',
            'released_at' => now(),
        ]);

        // Mark first step as in_progress
        $firstStep = $shopTicket->steps()->orderBy('sequence')->first();
        if ($firstStep) {
            $firstStep->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Shop ticket released to production.');
    }

    /**
     * Complete a specific step on the shop ticket.
     */
    public function completeStep(Request $request, ShopTicket $shopTicket, int $stepId): RedirectResponse
    {
        $step = $shopTicket->steps()->findOrFail($stepId);

        $validated = $request->validate([
            'actual_hours' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $step->update([
            'status' => 'complete',
            'actual_hours' => $validated['actual_hours'],
            'notes' => $validated['notes'] ?? null,
            'completed_at' => now(),
            'completed_by' => $request->input('completed_by'),
        ]);

        // Check if there's a next step and start it
        $nextStep = $shopTicket->steps()
            ->where('sequence', '>', $step->sequence)
            ->orderBy('sequence')
            ->first();

        if ($nextStep) {
            $nextStep->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);
        } else {
            // All steps complete, mark ticket as completed
            $shopTicket->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Step completed successfully.');
    }

    /**
     * Generate a unique ticket number.
     */
    protected function generateTicketNumber(): string
    {
        $prefix = 'ST-'.date('Ymd');
        $lastTicket = ShopTicket::where('ticket_number', 'like', $prefix.'%')
            ->orderByDesc('ticket_number')
            ->first();

        if ($lastTicket) {
            $lastNumber = (int) substr($lastTicket->ticket_number, -4);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return $prefix.'-'.$nextNumber;
    }
}
