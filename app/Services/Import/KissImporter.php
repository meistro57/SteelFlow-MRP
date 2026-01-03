<?php

// app/Services/Import/KissImporter.php

namespace App\Services\Import;

use App\Models\Assembly;
use App\Models\Part;
use App\Models\Project;
use App\Services\BOMExtensionService;
use App\Services\Pricing\WeightCalculator;
use App\Services\ReferenceDataService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class KissImporter
{
    protected array $errors = [];

    protected string $operationId;

    protected int $assemblyCount = 0;

    protected int $partCount = 0;

    public function __construct(
        protected ReferenceDataService $referenceData,
        protected WeightCalculator $weightCalculator,
        protected BOMExtensionService $bomExtensionService
    ) {}

    /**
     * Parse and import a KISS file.
     */
    public function import(string $filePath, Project $project): bool
    {
        $this->errors = [];
        $this->operationId = Str::uuid()->toString();
        $this->assemblyCount = 0;
        $this->partCount = 0;

        Log::info('Starting KISS import', [
            'operation_id' => $this->operationId,
            'project_id' => $project->id,
            'file' => $filePath,
        ]);

        if (! file_exists($filePath)) {
            $message = 'File not found: '.$filePath;
            $this->errors[] = $message;
            Log::error('KISS import failed - file missing', [
                'operation_id' => $this->operationId,
                'project_id' => $project->id,
                'file' => $filePath,
            ]);

            return false;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            $message = 'Unable to read file: '.$filePath;
            $this->errors[] = $message;
            Log::error('KISS import failed - unreadable file', [
                'operation_id' => $this->operationId,
                'project_id' => $project->id,
                'file' => $filePath,
            ]);

            return false;
        }

        $success = DB::transaction(function () use ($lines, $project) {
            foreach ($lines as $index => $line) {
                $this->processLine($line, $project, $index + 1);
            }

            return true;
        });

        if ($success) {
            $this->bomExtensionService->extendProject($project);
            Log::info('KISS import completed', [
                'operation_id' => $this->operationId,
                'project_id' => $project->id,
                'assemblies_processed' => $this->assemblyCount,
                'parts_processed' => $this->partCount,
                'errors' => $this->errors,
            ]);
        } else {
            Log::error('KISS import transaction failed', [
                'operation_id' => $this->operationId,
                'project_id' => $project->id,
                'errors' => $this->errors,
            ]);
        }

        return $success;
    }

    protected function processLine(string $line, Project $project, int $lineNumber = 0): void
    {
        $data = str_getcsv($line, ',');
        $type = strtoupper(trim($data[0] ?? ''));

        Log::debug('Processing KISS line', [
            'operation_id' => $this->operationId,
            'project_id' => $project->id,
            'line_number' => $lineNumber,
            'type' => $type,
        ]);

        switch ($type) {
            case 'SHP':
                $this->importAssembly($data, $project);
                break;
            case 'DET':
                $this->importPart($data, $project);
                break;
            default:
                Log::warning('Unknown KISS record type encountered', [
                    'operation_id' => $this->operationId,
                    'project_id' => $project->id,
                    'line_number' => $lineNumber,
                    'raw' => $line,
                ]);
                $this->errors[] = "Unknown record type at line {$lineNumber}";
                break;
        }
    }

    protected function importAssembly(array $data, Project $project): void
    {
        // Reference: SHP, Mark, Qty, Description, WeightEach, WeightTotal, Remark, Drawing
        $mark = trim($data[1] ?? '');
        if (! $mark) {
            Log::warning('Skipping assembly with empty mark', [
                'operation_id' => $this->operationId,
                'project_id' => $project->id,
            ]);

            return;
        }

        $assembly = Assembly::updateOrCreate(
            [
                'project_id' => $project->id,
                'mark' => $mark,
            ],
            [
                'quantity' => (int) ($data[2] ?? 1),
                'description' => trim($data[3] ?? ''),
                'weight_each_lbs' => (float) ($data[4] ?? 0),
                'total_weight_lbs' => (float) ($data[5] ?? 0),
                'weight_each_kg' => (float) ($data[4] ?? 0) * 0.453592,
                'total_weight_kg' => (float) ($data[5] ?? 0) * 0.453592,
            ]
        );

        $this->assemblyCount++;

        Log::info('Assembly processed from KISS import', [
            'operation_id' => $this->operationId,
            'project_id' => $project->id,
            'assembly_id' => $assembly->id,
            'mark' => $mark,
            'quantity' => (int) ($data[2] ?? 1),
        ]);
    }

    protected function importPart(array $data, Project $project): void
    {
        // Reference: DET, AssemblyMark, PartMark, QtyEach, Description, Material, Size, Length
        $assemblyMark = trim($data[1] ?? '');
        $partMark = trim($data[2] ?? '');

        $assembly = Assembly::where('project_id', $project->id)
            ->where('mark', $assemblyMark)
            ->first();

        if (! $assembly) {
            $this->errors[] = "Assembly $assemblyMark not found for part $partMark";
            Log::warning('KISS import unable to locate assembly', [
                'operation_id' => $this->operationId,
                'project_id' => $project->id,
                'assembly_mark' => $assemblyMark,
                'part_mark' => $partMark,
            ]);

            return;
        }

        $materialType = $this->parseMaterialType($data[6] ?? '');
        $material = $this->referenceData->findMaterial($materialType, trim($data[6] ?? ''));
        $grade = $this->referenceData->findOrCreateGrade(trim($data[5] ?? 'A36'));

        $length = $this->parseLength(trim($data[7] ?? '0'));

        $part = Part::updateOrCreate(
            [
                'project_id' => $project->id,
                'assembly_id' => $assembly->id,
                'part_mark' => $partMark,
            ],
            [
                'material_id' => $material?->id,
                'type' => $materialType,
                'size_imperial' => trim($data[6] ?? ''),
                'grade' => $grade->code,
                'length' => $length,
                'quantity' => (int) ($data[3] ?? 1),
                'description' => trim($data[4] ?? ''),
                'weight_each_lbs' => (float) ($data[8] ?? 0),
                'total_weight_lbs' => (float) ($data[9] ?? 0),
            ]
        );

        $this->partCount++;

        Log::info('Part processed from KISS import', [
            'operation_id' => $this->operationId,
            'project_id' => $project->id,
            'part_id' => $part->id,
            'assembly_id' => $assembly->id,
            'part_mark' => $partMark,
            'quantity' => (int) ($data[3] ?? 1),
            'material_type' => $materialType,
            'length' => $length,
        ]);

        // Recalculate weights if zero provided in file
        if ($part->weight_each_lbs <= 0 && $material) {
            $weights = $this->weightCalculator->calculatePartWeights($part);
            $part->update($weights);

            Log::info('Part weight recalculated during KISS import', [
                'operation_id' => $this->operationId,
                'project_id' => $project->id,
                'part_id' => $part->id,
                'updated_weights' => $weights,
            ]);
        }
    }

    protected function parseLength(string $lengthStr): float
    {
        if (str_contains($lengthStr, '-')) {
            $parts = explode('-', $lengthStr);
            if (count($parts) === 3) {
                $feet = (float) $parts[0];
                $inches = (float) $parts[1];
                $sixteenths = (float) $parts[2];

                return ($feet * 12) + $inches + ($sixteenths / 16);
            }
        }

        return (float) $lengthStr;
    }

    protected function parseMaterialType(string $size): string
    {
        if (str_starts_with($size, 'W')) {
            return 'W';
        }
        if (str_starts_with($size, 'L')) {
            return 'L';
        }
        if (str_starts_with($size, 'C')) {
            return 'C';
        }
        if (str_starts_with($size, 'HSS')) {
            return 'HSS';
        }
        if (str_starts_with($size, 'PL')) {
            return 'PL';
        }

        return 'OT';
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
