<?php
// app/Services/Import/XsrImporter.php

namespace App\Services\Import;

use App\Models\Project;
use App\Models\Assembly;
use App\Models\Part;
use App\Services\BOMExtensionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class XsrImporter
{
    protected array $errors = [];
    protected string $operationId;
    protected int $assemblyCount = 0;
    protected int $partCount = 0;

    public function __construct(
        protected BOMExtensionService $bomExtensionService
    ) {}

    /**
     * Parse and import an XSR file
     */
    public function import(string $filePath, Project $project)
    {
        $this->errors = [];
        $this->operationId = Str::uuid()->toString();
        $this->assemblyCount = 0;
        $this->partCount = 0;

        Log::info('Starting XSR import', [
            'operation_id' => $this->operationId,
            'project_id' => $project->id,
            'file' => $filePath,
        ]);

        if (!file_exists($filePath)) {
            $this->errors[] = "File not found: {$filePath}";
            Log::error('XSR import failed - file missing', [
                'operation_id' => $this->operationId,
                'project_id' => $project->id,
                'file' => $filePath,
            ]);
            return false;
        }

        $handle = fopen($filePath, "r");
        if (!$handle) {
            $this->errors[] = "Unable to open file: {$filePath}";
            Log::error('XSR import failed - cannot open file', [
                'operation_id' => $this->operationId,
                'project_id' => $project->id,
                'file' => $filePath,
            ]);
            return false;
        }

        $header = fgetcsv($handle); // Assume first row is header
        $map = array_flip($header);

        $success = DB::transaction(function () use ($handle, $project, $map) {
            while (($parts = fgetcsv($handle)) !== false) {
                if (count($parts) < 2) {
                    Log::warning('Skipping malformed XSR row', [
                        'operation_id' => $this->operationId,
                        'project_id' => $project->id,
                        'raw' => $parts,
                    ]);
                    continue;
                }

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
                } else {
                    $this->errors[] = 'Encountered row without part mark';
                    Log::warning('XSR row missing part mark', [
                        'operation_id' => $this->operationId,
                        'project_id' => $project->id,
                        'raw' => $parts,
                    ]);
                }
            }
            fclose($handle);
            return true;
        });

        if ($success) {
            $this->bomExtensionService->extendProject($project);
            Log::info('XSR import completed', [
                'operation_id' => $this->operationId,
                'project_id' => $project->id,
                'assemblies_processed' => $this->assemblyCount,
                'parts_processed' => $this->partCount,
                'errors' => $this->errors,
            ]);
        } else {
            Log::error('XSR import transaction failed', [
                'operation_id' => $this->operationId,
                'project_id' => $project->id,
                'errors' => $this->errors,
            ]);
        }

        return $success;
    }

    protected function processData(array $data, Project $project)
    {
        $assembly = Assembly::firstOrCreate([
            'project_id' => $project->id,
            'mark' => $data['assembly'] ?: 'DETACHED'
        ]);

        $this->assemblyCount++;

        Log::info('XSR assembly processed', [
            'operation_id' => $this->operationId,
            'project_id' => $project->id,
            'assembly_id' => $assembly->id,
            'mark' => $data['assembly'] ?: 'DETACHED',
        ]);

        $part = Part::create([
            'assembly_id' => $assembly->id,
            'project_id'  => $project->id,
            'part_mark'   => $data['mark'],
            'quantity'    => $data['quantity'],
            'grade'       => $data['material'],
            'size_imperial' => $data['shape'],
            'length'      => $data['length'],
            'weight_each_lbs' => $data['weight'],
        ]);

        $this->partCount++;

        Log::info('XSR part processed', [
            'operation_id' => $this->operationId,
            'project_id' => $project->id,
            'assembly_id' => $assembly->id,
            'part_id' => $part->id,
            'part_mark' => $data['mark'],
            'quantity' => $data['quantity'],
        ]);
    }
}
