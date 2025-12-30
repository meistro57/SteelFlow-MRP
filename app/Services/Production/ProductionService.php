<?php

namespace App\Services\Production;

use App\Models\PartWorkArea;
use App\Models\TimeEntry;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductionService
{
    /**
     * Start work on a routing step.
     */
    public function startWork(PartWorkArea $step, Employee $employee): void
    {
        $step->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        // Create initial time entry
        TimeEntry::create([
            'employee_id' => $employee->id,
            'project_id' => $step->partInstance->project_id,
            'part_work_area_id' => $step->id,
            'work_area_id' => $step->work_area_id,
            'batch_id' => $step->batch_id,
            'start_time' => now(),
        ]);
    }

    /**
     * Complete work on a routing step.
     */
    public function completeWork(PartWorkArea $step, Employee $employee, array $data = []): void
    {
        DB::transaction(function () use ($step, $employee, $data) {
            $now = now();

            // 1. Close open time entries
            $openEntry = TimeEntry::where('part_work_area_id', $step->id)
                ->where('employee_id', $employee->id)
                ->whereNull('end_time')
                ->first();

            if ($openEntry) {
                $startTime = Carbon::parse($openEntry->start_time);
                $hours = $startTime->diffInMinutes($now) / 60;
                
                $openEntry->update([
                    'end_time' => $now,
                    'hours' => $hours,
                    'quantity' => $data['quantity'] ?? 1,
                ]);
            }

            // 2. Update routing step status
            $step->update([
                'status' => 'complete',
                'completed_at' => $now,
                'completed_by' => $employee->id,
                'actual_hours' => ($step->actual_hours ?? 0) + ($hours ?? 0),
            ]);

            // 3. Trigger events for assembly completion if all parts done
            // $this->checkAssemblyProgress($step->assembly_instance_id);
        });
    }
}
