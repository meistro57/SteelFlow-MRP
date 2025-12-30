<?php

namespace App\Services\Import;

use App\Models\Project;
use App\Models\Assembly;
use App\Models\Part;
use App\Services\ReferenceDataService;
use App\Services\Pricing\WeightCalculator;
use App\Services\BOMExtensionService;
use Illuminate\Support\Facades\DB;

class KissImporter
{
    protected array $errors = [];

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
        if (!file_exists($filePath)) {
            $this->errors[] = "File not found: " . $filePath;
            return false;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        $success = DB::transaction(function () use ($lines, $project) {
            foreach ($lines as $line) {
                $this->processLine($line, $project);
            }
            return true;
        });

        if ($success) {
            $this->bomExtensionService->extendProject($project);
        }

        return $success;
    }

    protected function processLine(string $line, Project $project): void
    {
        $data = str_getcsv($line, ',');
        $type = strtoupper(trim($data[0] ?? ''));

        switch ($type) {
            case 'SHP':
                $this->importAssembly($data, $project);
                break;
            case 'DET':
                $this->importPart($data, $project);
                break;
        }
    }

    protected function importAssembly(array $data, Project $project): void
    {
        // Reference: SHP, Mark, Qty, Description, WeightEach, WeightTotal, Remark, Drawing
        $mark = trim($data[1] ?? '');
        if (!$mark) return;

        Assembly::updateOrCreate(
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
    }

    protected function importPart(array $data, Project $project): void
    {
        // Reference: DET, AssemblyMark, PartMark, QtyEach, Description, Material, Size, Length
        $assemblyMark = trim($data[1] ?? '');
        $partMark = trim($data[2] ?? '');
        
        $assembly = Assembly::where('project_id', $project->id)
            ->where('mark', $assemblyMark)
            ->first();

        if (!$assembly) {
            $this->errors[] = "Assembly $assemblyMark not found for part $partMark";
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

        // Recalculate weights if zero provided in file
        if ($part->weight_each_lbs <= 0 && $material) {
            $weights = $this->weightCalculator->calculatePartWeights($part);
            $part->update($weights);
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
        if (str_starts_with($size, 'W')) return 'W';
        if (str_starts_with($size, 'L')) return 'L';
        if (str_starts_with($size, 'C')) return 'C';
        if (str_starts_with($size, 'HSS')) return 'HSS';
        if (str_starts_with($size, 'PL')) return 'PL';
        return 'OT';
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
