<?php

namespace Database\Seeders;

use App\Enums\JobStatus;
use App\Enums\MaterialType;
use App\Enums\PartStatus;
use App\Models\BomItem;
use App\Models\FabJob;
use App\Models\FabPart;
use App\Models\RawMaterial;
use Illuminate\Database\Seeder;

class FilamentMrpSeeder extends Seeder
{
    public function run(): void
    {
        // Create Raw Materials
        $materials = [
            ['type' => MaterialType::Beam, 'size' => 'W10x26', 'grade' => 'A992', 'length' => 40, 'qty' => 25, 'location' => 'Bay A-1'],
            ['type' => MaterialType::Beam, 'size' => 'W12x40', 'grade' => 'A992', 'length' => 40, 'qty' => 18, 'location' => 'Bay A-2'],
            ['type' => MaterialType::Beam, 'size' => 'W14x22', 'grade' => 'A992', 'length' => 40, 'qty' => 12, 'location' => 'Bay A-3'],
            ['type' => MaterialType::Beam, 'size' => 'W16x31', 'grade' => 'A992', 'length' => 40, 'qty' => 3, 'location' => 'Bay A-4'], // Low stock
            ['type' => MaterialType::Channel, 'size' => 'C8x11.5', 'grade' => 'A36', 'length' => 20, 'qty' => 30, 'location' => 'Bay B-1'],
            ['type' => MaterialType::Channel, 'size' => 'C10x15.3', 'grade' => 'A36', 'length' => 20, 'qty' => 2, 'location' => 'Bay B-2'], // Low stock
            ['type' => MaterialType::Angle, 'size' => 'L4x4x1/4', 'grade' => 'A36', 'length' => 20, 'qty' => 50, 'location' => 'Bay C-1'],
            ['type' => MaterialType::Angle, 'size' => 'L5x5x3/8', 'grade' => 'A36', 'length' => 20, 'qty' => 35, 'location' => 'Bay C-2'],
            ['type' => MaterialType::Tube, 'size' => 'HSS6x6x1/4', 'grade' => 'A500B', 'length' => 24, 'qty' => 15, 'location' => 'Bay D-1'],
            ['type' => MaterialType::Tube, 'size' => 'HSS8x8x3/8', 'grade' => 'A500B', 'length' => 24, 'qty' => 8, 'location' => 'Bay D-2'],
            ['type' => MaterialType::Plate, 'size' => 'PL1/2x48', 'grade' => 'A36', 'length' => 96, 'qty' => 0, 'location' => 'Bay E-1'], // Out of stock
            ['type' => MaterialType::Plate, 'size' => 'PL3/4x48', 'grade' => 'A572-50', 'length' => 96, 'qty' => 10, 'location' => 'Bay E-2'],
            ['type' => MaterialType::Pipe, 'size' => 'PIPE4STD', 'grade' => 'A53B', 'length' => 21, 'qty' => 20, 'location' => 'Bay F-1'],
            ['type' => MaterialType::Bar, 'size' => '1x4 FB', 'grade' => 'A36', 'length' => 20, 'qty' => 100, 'location' => 'Bay G-1'],
        ];

        $materialRecords = [];
        foreach ($materials as $mat) {
            $materialRecords[$mat['size']] = RawMaterial::create([
                'material_type' => $mat['type'],
                'size_description' => $mat['size'],
                'grade' => $mat['grade'],
                'length_stock' => $mat['length'],
                'quantity_on_hand' => $mat['qty'],
                'location' => $mat['location'],
            ]);
        }

        // Create Jobs with various statuses
        $jobs = [
            [
                'job_number' => 'FAB-2024-001',
                'customer_name' => 'ACME Construction Corp',
                'description' => 'Downtown Office Building - Steel Frame Package',
                'status' => JobStatus::InProgress,
                'due_date' => now()->addDays(30),
            ],
            [
                'job_number' => 'FAB-2024-002',
                'customer_name' => 'Bridge Builders Inc',
                'description' => 'River Crossing Bridge Deck Supports',
                'status' => JobStatus::Awarded,
                'due_date' => now()->addDays(60),
            ],
            [
                'job_number' => 'FAB-2024-003',
                'customer_name' => 'Metro Steel Erectors',
                'description' => 'Parking Garage Level 1 Columns and Beams',
                'status' => JobStatus::Shipping,
                'due_date' => now()->addDays(5),
            ],
            [
                'job_number' => 'FAB-2024-004',
                'customer_name' => 'Industrial Fabricators LLC',
                'description' => 'Conveyor Support Structure',
                'status' => JobStatus::Estimating,
                'due_date' => now()->addDays(90),
            ],
            [
                'job_number' => 'FAB-2024-005',
                'customer_name' => 'Summit Steel Works',
                'description' => 'Warehouse Mezzanine Platform',
                'status' => JobStatus::Complete,
                'due_date' => now()->subDays(5),
            ],
            [
                'job_number' => 'FAB-2024-006',
                'customer_name' => 'Pacific Construction Group',
                'description' => 'Retail Building Canopy Frames',
                'status' => JobStatus::Hold,
                'due_date' => now()->addDays(45),
            ],
        ];

        foreach ($jobs as $jobData) {
            $job = FabJob::create($jobData);

            // Create parts for each job
            $this->createPartsForJob($job, $materialRecords);
        }

        $this->command->info('Filament MRP sample data seeded successfully!');
    }

    private function createPartsForJob(FabJob $job, array $materials): void
    {
        /** @var JobStatus $status */
        $status = $job->status;

        $partsData = match ($status) {
            JobStatus::InProgress => [
                ['mark' => 'B1', 'desc' => 'Main Beam - Grid A', 'qty' => 4, 'grade' => 'A992', 'weight' => 520, 'status' => PartStatus::Fabrication, 'material' => 'W12x40'],
                ['mark' => 'B2', 'desc' => 'Secondary Beam', 'qty' => 8, 'grade' => 'A992', 'weight' => 260, 'status' => PartStatus::Cutting, 'material' => 'W10x26'],
                ['mark' => 'C1', 'desc' => 'Column - Interior', 'qty' => 6, 'grade' => 'A992', 'weight' => 440, 'status' => PartStatus::Pending, 'material' => 'W14x22'],
                ['mark' => 'BR1', 'desc' => 'Diagonal Brace', 'qty' => 12, 'grade' => 'A500B', 'weight' => 95, 'status' => PartStatus::Nested, 'material' => 'HSS6x6x1/4'],
            ],
            JobStatus::Awarded => [
                ['mark' => 'GB1', 'desc' => 'Girder - Span 1', 'qty' => 2, 'grade' => 'A992', 'weight' => 1240, 'status' => PartStatus::Pending, 'material' => 'W16x31'],
                ['mark' => 'GB2', 'desc' => 'Girder - Span 2', 'qty' => 2, 'grade' => 'A992', 'weight' => 1240, 'status' => PartStatus::Pending, 'material' => 'W16x31'],
                ['mark' => 'STF1', 'desc' => 'Stiffener Plate', 'qty' => 16, 'grade' => 'A572-50', 'weight' => 45, 'status' => PartStatus::Pending, 'material' => 'PL3/4x48'],
            ],
            JobStatus::Shipping => [
                ['mark' => 'PC1', 'desc' => 'Parking Column', 'qty' => 10, 'grade' => 'A992', 'weight' => 380, 'status' => PartStatus::Complete, 'material' => 'W12x40'],
                ['mark' => 'PB1', 'desc' => 'Parking Beam', 'qty' => 20, 'grade' => 'A992', 'weight' => 520, 'status' => PartStatus::Complete, 'material' => 'W10x26'],
                ['mark' => 'PB2', 'desc' => 'Edge Beam', 'qty' => 8, 'grade' => 'A36', 'weight' => 230, 'status' => PartStatus::Shipped, 'material' => 'C10x15.3'],
            ],
            JobStatus::Complete => [
                ['mark' => 'M1', 'desc' => 'Mezzanine Column', 'qty' => 8, 'grade' => 'A500B', 'weight' => 180, 'status' => PartStatus::Shipped, 'material' => 'HSS8x8x3/8'],
                ['mark' => 'M2', 'desc' => 'Mezzanine Beam', 'qty' => 12, 'grade' => 'A992', 'weight' => 260, 'status' => PartStatus::Shipped, 'material' => 'W10x26'],
            ],
            default => [
                ['mark' => 'P1', 'desc' => 'Generic Part 1', 'qty' => 4, 'grade' => 'A36', 'weight' => 150, 'status' => PartStatus::Pending, 'material' => 'L4x4x1/4'],
                ['mark' => 'P2', 'desc' => 'Generic Part 2', 'qty' => 6, 'grade' => 'A36', 'weight' => 85, 'status' => PartStatus::Pending, 'material' => 'C8x11.5'],
            ],
        };

        foreach ($partsData as $partData) {
            $part = FabPart::create([
                'fab_job_id' => $job->id,
                'mark_number' => $partData['mark'],
                'description' => $partData['desc'],
                'quantity' => $partData['qty'],
                'material_grade' => $partData['grade'],
                'weight_each' => $partData['weight'],
                'status' => $partData['status'],
            ]);

            // Create BOM item linking to material
            if (isset($materials[$partData['material']])) {
                BomItem::create([
                    'fab_part_id' => $part->id,
                    'raw_material_id' => $materials[$partData['material']]->id,
                    'quantity_required' => $partData['qty'],
                    'cut_length' => rand(8, 20) + (rand(0, 100) / 100),
                    'notes' => null,
                ]);
            }
        }
    }
}
