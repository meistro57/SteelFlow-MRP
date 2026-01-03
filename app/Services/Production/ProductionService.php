<?php

// app/Services/Production/ProductionService.php

namespace App\Services\Production;

use App\Models\Employee;
use App\Models\PartWorkArea;
use App\Models\TimeEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductionService
{
    /**
     * Start work on a routing step.
     */
    public function startWork(PartWorkArea $step, Employee $employee): void
    {
        $operationId = Str::uuid()->toString();

        Log::info('Starting work on routing step', [
            'operation_id' => $operationId,
            'step_id' => $step->id,
            'work_area_id' => $step->work_area_id,
            'employee_id' => $employee->id,
        ]);

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

        Log::info('Routing step started', [
            'operation_id' => $operationId,
            'step_id' => $step->id,
            'employee_id' => $employee->id,
        ]);
    }

    /**
     * Complete work on a routing step.
     */
    public function completeWork(PartWorkArea $step, Employee $employee, array $data = []): void
    {
        $operationId = Str::uuid()->toString();

        Log::info('Completing routing step', [
            'operation_id' => $operationId,
            'step_id' => $step->id,
            'employee_id' => $employee->id,
            'payload' => $data,
        ]);

        DB::transaction(function () use ($step, $employee, $data, $operationId) {
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

                Log::info('Time entry closed for routing step', [
                    'operation_id' => $operationId,
                    'time_entry_id' => $openEntry->id,
                    'hours_recorded' => $hours,
                ]);
            } else {
                Log::warning('No open time entry found during completion', [
                    'operation_id' => $operationId,
                    'step_id' => $step->id,
                    'employee_id' => $employee->id,
                ]);
            }

            // 2. Update routing step status
            $step->update([
                'status' => 'complete',
                'completed_at' => $now,
                'completed_by' => $employee->id,
                'actual_hours' => ($step->actual_hours ?? 0) + ($hours ?? 0),
            ]);

            Log::info('Routing step completed', [
                'operation_id' => $operationId,
                'step_id' => $step->id,
                'employee_id' => $employee->id,
                'duration_hours' => $hours ?? 0,
            ]);

            // 3. Trigger events for assembly completion if all parts done
            // $this->checkAssemblyProgress($step->assembly_instance_id);
        });
    }
}
