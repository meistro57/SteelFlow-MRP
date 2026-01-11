<?php

namespace Modules\UPF\Services;

use Modules\UPF\Models\MaterialType;
use Modules\UPF\Models\MaterialGrade;
use Modules\UPF\Models\UpfPrice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FabTrolImporter
{
    public function __construct(
        protected KeyGenerationService $keyGen
    ) {}

    /**
     * Import UPF data from a CSV file (exported from FabTrol DBF)
     */
    public function importUPF(string $csvPath): array
    {
        $stats = ['types' => 0, 'grades' => 0, 'prices' => 0, 'errors' => []];
        
        if (!file_exists($csvPath)) {
            $stats['errors'][] = "File not found: $csvPath";
            return $stats;
        }

        $handle = fopen($csvPath, 'r');
        $header = fgetcsv($handle);

        if (!$header) {
            $stats['errors'][] = "Empty or invalid CSV file";
            return $stats;
        }

        // Normalize header to uppercase
        $header = array_map('strtoupper', $header);

        DB::transaction(function () use ($handle, $header, &$stats) {
            $rowNum = 1;
            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;
                try {
                    // Check if row has same columns as header
                    if (count($header) !== count($row)) {
                        $stats['errors'][] = "Row {$rowNum}: Column count mismatch";
                        continue;
                    }

                    $data = array_combine($header, $row);
                    
                    // 1. Process Type
                    $type = trim($data['TYPE'] ?? $data['TYPE_NAME'] ?? '');
                    if (!$type) continue;

                    $materialType = MaterialType::firstOrCreate(
                        ['type' => $type],
                        [
                            'title' => $data['TYPEDESC'] ?? $data['DESCRIPTION'] ?? $type,
                            'description' => $data['TYPEDESC'] ?? $data['DESCRIPTION'] ?? '',
                            'pkey' => $this->keyGen->generatePKey('MAT'),
                            'is_active' => true
                        ]
                    );
                    $stats['types']++;

                    // 2. Process Grade
                    $gradeName = trim($data['GRADE'] ?? $data['GRADE_NAME'] ?? '');
                    $materialGrade = null;
                    if ($gradeName) {
                        $materialGrade = MaterialGrade::firstOrCreate(
                            ['material_type_id' => $materialType->id, 'grade_name' => $gradeName],
                            [
                                'type' => $type,
                                'pkey' => $this->keyGen->generatePKey('GRD'),
                                'is_active' => true
                            ]
                        );
                        $stats['grades']++;
                    }

                    // 3. Process Price Item
                    $size = trim($data['SIZE'] ?? '');
                    if ($size) {
                        UpfPrice::updateOrCreate(
                            ['type' => $type, 'size' => $size, 'grade_name' => $gradeName],
                            [
                                'material_type_id' => $materialType->id,
                                'material_grade_id' => $materialGrade?->id,
                                'pkey' => $data['PKEY'] ?? $this->keyGen->generatePKey('UPF'),
                                'filekey' => $data['FILEKEY'] ?? $this->keyGen->getNextFileKey(),
                                'orderkey' => $data['ORDERKEY'] ?? $this->keyGen->getNextOrderKey(),
                                'unit_price' => (float) ($data['PRICE'] ?? $data['UNIT_PRICE'] ?? 0),
                                'price_unit' => $data['PUNIT'] ?? $data['UNIT'] ?? 'LB',
                                'nominal_thickness' => (float) ($data['THICK'] ?? $data['THICKNESS'] ?? 0),
                                'weight_per_foot' => (float) ($data['WTFT'] ?? $data['WEIGHT_FT'] ?? 0),
                                'is_active' => true
                            ]
                        );
                        $stats['prices']++;
                    }
                } catch (\Exception $e) {
                    $stats['errors'][] = "Row {$rowNum}: " . $e->getMessage();
                }
            }
        });

        fclose($handle);
        return $stats;
    }
}
