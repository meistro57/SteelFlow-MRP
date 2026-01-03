<?php

namespace App\Services;

use App\Models\Assembly;
use App\Models\Part;
use App\Models\PartInstance;
use App\Models\Project;
use App\Services\Pricing\WeightCalculator;
use Illuminate\Support\Facades\DB;

class BOMExtensionService
{
    public function __construct(
        protected WeightCalculator $weightCalculator
    ) {}

    /**
     * Extend a project: Calculate all weights and generate instances.
     */
    public function extendProject(Project $project): void
    {
        DB::transaction(function () use ($project) {
            foreach ($project->assemblies as $assembly) {
                $this->extendAssembly($assembly);
            }
        });
    }

    /**
     * Extend an assembly:
     * 1. Calculate weights for all parts
     * 2. Calculate total assembly weight
     * 3. Sync assembly instances
     */
    public function extendAssembly(Assembly $assembly): void
    {
        DB::transaction(function () use ($assembly) {
            $totalLbs = 0;
            $totalKg = 0;

            // 1. Process Parts
            foreach ($assembly->parts as $part) {
                $weights = $this->weightCalculator->calculatePartWeights($part);
                $part->update($weights);

                $totalLbs += $weights['total_weight_lbs'];
                $totalKg += $weights['total_weight_kg'];
            }

            // 2. Update Assembly Weights
            $assembly->update([
                'total_weight_lbs' => $totalLbs,
                'total_weight_kg' => $totalKg,
                'weight_each_lbs' => $assembly->quantity > 0 ? $totalLbs / $assembly->quantity : 0,
                'weight_each_kg' => $assembly->quantity > 0 ? $totalKg / $assembly->quantity : 0,
            ]);

            // 3. Sync Instances
            $this->syncInstances($assembly);
        });
    }

    /**
     * Ensure we have the correct number of instances for the assembly.
     */
    protected function syncInstances(Assembly $assembly): void
    {
        $currentCount = $assembly->instances()->count();
        $targetCount = $assembly->quantity;

        if ($currentCount < $targetCount) {
            // Create missing
            for ($i = $currentCount + 1; $i <= $targetCount; $i++) {
                $instance = $assembly->instances()->create([
                    'project_id' => $assembly->project_id,
                    'instance_number' => $i,
                    'status' => 'not_started',
                ]);

                // Create part instances for this assembly instance
                foreach ($assembly->parts as $part) {
                    for ($p = 1; $p <= $part->quantity; $p++) {
                        PartInstance::create([
                            'part_id' => $part->id,
                            'assembly_instance_id' => $instance->id,
                            'project_id' => $assembly->project_id,
                            'status' => 'not_started',
                        ]);
                    }
                }
            }
        } elseif ($currentCount > $targetCount) {
            // Remove extra (caution: only if not started)
            $assembly->instances()
                ->where('instance_number', '>', $targetCount)
                ->where('status', 'not_started')
                ->delete();
        }
    }
}
