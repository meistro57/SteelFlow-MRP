<?php

namespace Modules\UPF\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\UPF\Models\MaterialType;
use Modules\UPF\Models\MaterialGrade;
use Modules\UPF\Models\UpfPrice;
use Modules\UPF\Services\KeyGenerationService;

class UPFDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $keyGen = app(KeyGenerationService::class);

        // 1. Standard Steel Types
        $types = [
            'PLATE' => ['title' => 'Hot Rolled Steel Plate', 'desc' => 'Standard steel plate materials', 'uom' => 'LB'],
            'WBEAM' => ['title' => 'Wide Flange Beam', 'desc' => 'I-Beams / Wide Flange sections', 'uom' => 'LB'],
            'ANGLE' => ['title' => 'Steel Angle', 'desc' => 'L-shaped steel sections', 'uom' => 'LB'],
            'HSS' => ['title' => 'Hollow Structural Section', 'desc' => 'Square and rectangular tubing', 'uom' => 'LB'],
        ];

        foreach ($types as $code => $info) {
            $type = MaterialType::firstOrCreate(
                ['type' => $code],
                [
                    'title' => $info['title'],
                    'description' => $info['desc'],
                    'pkey' => $keyGen->generatePKey('MAT'),
                    'is_active' => true
                ]
            );

            // 2. Standard Grades per Type
            $grades = [];
            if ($code === 'PLATE' || $code === 'ANGLE') {
                $grades = ['A36'];
            } elseif ($code === 'WBEAM') {
                $grades = ['A992', 'A572-50'];
            } elseif ($code === 'HSS') {
                $grades = ['A500-B'];
            }

            foreach ($grades as $gradeName) {
                $materialGrade = MaterialGrade::firstOrCreate(
                    ['material_type_id' => $type->id, 'grade_name' => $gradeName],
                    [
                        'type' => $code,
                        'pkey' => $keyGen->generatePKey('GRD'),
                        'is_active' => true
                    ]
                );

                // 3. Standard Sizes & Prices
                $this->seedSizes($type, $materialGrade, $keyGen);
            }
        }
    }

    protected function seedSizes($type, $grade, $keyGen)
    {
        $sizes = [];
        if ($type->type === 'PLATE') {
            $sizes = [
                ['s' => '1/4', 'thick' => 0.25, 'wt' => 10.21],
                ['s' => '1/2', 'thick' => 0.50, 'wt' => 20.42],
                ['s' => '3/4', 'thick' => 0.75, 'wt' => 30.63],
                ['s' => '1', 'thick' => 1.00, 'wt' => 40.84],
            ];
        } elseif ($type->type === 'WBEAM') {
            $sizes = [
                ['s' => 'W14X22', 'wt' => 22.0],
                ['s' => 'W16X26', 'wt' => 26.0],
                ['s' => 'W18X35', 'wt' => 35.0],
            ];
        }

        foreach ($sizes as $sInfo) {
            UpfPrice::firstOrCreate(
                ['type' => $type->type, 'size' => $sInfo['s'], 'grade_name' => $grade->grade_name],
                [
                    'material_type_id' => $type->id,
                    'material_grade_id' => $grade->id,
                    'pkey' => $keyGen->generatePKey('UPF'),
                    'filekey' => $keyGen->getNextFileKey(),
                    'orderkey' => $keyGen->getNextOrderKey(),
                    'unit_price' => 0.55,
                    'price_unit' => 'LB',
                    'nominal_thickness' => $sInfo['thick'] ?? 0,
                    'weight_per_foot' => $sInfo['wt'],
                    'is_active' => true,
                    'is_stocked' => true
                ]
            );
        }
    }
}
