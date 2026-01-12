<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PartWorkArea;
use App\Models\TimeEntry;
use App\Services\Production\ProductionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TimeEntryController extends Controller
{
    public function __construct(
        protected ProductionService $productionService,
    ) {}

    public function index(): Response
    {
        $timeEntries = TimeEntry::with(['employee', 'project', 'workArea'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Production/TimeEntries', [
            'timeEntries' => $timeEntries,
            'employees' => Employee::all(),
        ]);
    }

    public function start(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'part_work_area_id' => 'required|exists:part_work_areas,id',
            'employee_id' => 'required|exists:employees,id',
        ]);

        $step = PartWorkArea::find($validated['part_work_area_id']);
        $employee = Employee::find($validated['employee_id']);

        $this->productionService->startWork($step, $employee);

        return back()->with('success', 'Work started.');
    }

    public function complete(Request $request, PartWorkArea $step): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $employee = Employee::find($validated['employee_id']);

        $this->productionService->completeWork($step, $employee, $validated);

        return back()->with('success', 'Work completed.');
    }
}
