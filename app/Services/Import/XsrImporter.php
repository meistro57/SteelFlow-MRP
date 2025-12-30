<?php

namespace App\Services\Import;

use App\Models\Project;
use App\Models\Assembly;
use App\Models\Part;
use App\Services\BOMExtensionService;
use Illuminate\Support\Facades\DB;

class XsrImporter
{
    public function __construct(
        protected BOMExtensionService $bomExtensionService
    ) {}

    /**
     * Parse and import an XSR file
     */
    public function import(string $filePath, Project $project)
    {
        $handle = fopen($filePath, "r");
        if (!$handle) return false;

        $header = fgetcsv($handle); // Assume first row is header
        $map = array_flip($header);

        $success = DB::transaction(function () use ($handle, $project, $map) {
            while (($parts = fgetcsv($handle)) !== false) {
                if (count($parts) < 2) continue;
                
                $data = [
                    'assembly' => $parts[$map['AssemblyMark'] ?? $map['Assembly'] ?? 0] ?? '',
                    'mark'     => $parts[$map['PartMark'] ?? $map['Mark'] ?? 1] ?? '',
                    'quantity' => (int)($parts[$map['Quantity'] ?? $map['Qty'] ?? 2] ?? 1),
                    'material' => $parts[$map['Material'] ?? $map['Grade'] ?? 3] ?? 'A36',
                    'shape'    => $parts[$map['Shape'] ?? $map['Size'] ?? 4] ?? '',
                    'length'   => (float)($parts[$map['Length'] ?? 5] ?? 0),
                    'weight'   => (float)($parts[$map['Weight'] ?? 6] ?? 0),
                ];

                if ($data['mark']) {
                    $this->processData($data, $project);
                }
            }
            fclose($handle);
            return true;
        });

        if ($success) {
            $this->bomExtensionService->extendProject($project);
        }

        return $success;
    }

    protected function processData(array $data, Project $project)
    {
        $assembly = Assembly::firstOrCreate([
            'project_id' => $project->id,
            'mark' => $data['assembly'] ?: 'DETACHED'
        ]);

        Part::create([
            'assembly_id' => $assembly->id,
            'project_id'  => $project->id,
            'part_mark'   => $data['mark'],
            'quantity'    => $data['quantity'],
            'grade'       => $data['material'],
            'size_imperial' => $data['shape'],
            'length'      => $data['length'],
            'weight_each_lbs' => $data['weight'],
        ]);
    }
}
