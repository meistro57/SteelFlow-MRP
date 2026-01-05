<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StockItemSeeder extends Seeder
{
    /**
     * Stock areas available in the system.
     */
    private array $stockAreas = ['yard_a', 'yard_b', 'warehouse', 'production'];

    /**
     * Common stock lengths in inches by material type.
     */
    private array $standardLengths = [
        'W' => [240, 360, 480, 600],      // 20', 30', 40', 50'
        'S' => [240, 360, 480],            // 20', 30', 40'
        'C' => [240, 360, 480],            // 20', 30', 40'
        'MC' => [240, 360],                // 20', 30'
        'L' => [240, 480],                 // 20', 40'
        'HSS' => [240, 288, 480],          // 20', 24', 40'
        'PIPE' => [252, 480],              // 21', 40'
        'HP' => [360, 480, 600],           // 30', 40', 50'
        'WT' => [240, 360],                // 20', 30'
        'PLATE' => [96, 120, 144, 240],    // 8', 10', 12', 20'
        'BAR' => [240],                    // 20'
    ];

    /**
     * Sample heat numbers for material traceability.
     */
    private array $heatNumbers = [
        'HEAT-A7834-2024',
        'HEAT-B9012-2024',
        'HEAT-C3456-2024',
        'HEAT-D7890-2024',
        'HEAT-E1234-2024',
        'HEAT-F5678-2024',
        'HEAT-G9012-2023',
        'HEAT-H3456-2023',
        'HEAT-J7890-2023',
        'HEAT-K1234-2023',
    ];

    /**
     * Sample PO numbers.
     */
    private array $poNumbers = [
        'PO-2024-001',
        'PO-2024-002',
        'PO-2024-003',
        'PO-2024-004',
        'PO-2024-005',
        'PO-2023-098',
        'PO-2023-099',
        'PO-2023-100',
    ];

    /**
     * Countries of origin.
     */
    private array $countries = [
        'USA',
        'USA',
        'USA',
        'USA',     // Weight towards domestic
        'Canada',
        'Mexico',
        'Germany',
        'Japan',
    ];

    /**
     * Seed the stock_items table with realistic inventory.
     */
    public function run(): void
    {
        // Get materials grouped by type
        $materials = DB::table('materials')
            ->join('grades', 'materials.grade_id', '=', 'grades.id')
            ->select('materials.*', 'grades.code as grade_code')
            ->where('materials.is_active', true)
            ->get()
            ->groupBy('type');

        // Get project IDs for assigning stock
        $projectIds = DB::table('projects')->pluck('id')->toArray();

        $stockItems = [];
        $movements = [];
        $stockCount = 0;

        // Define how many items to create per type
        $itemsPerType = [
            'W' => 60,      // Wide flange - most common
            'HSS' => 35,    // Hollow sections - very common
            'L' => 25,      // Angles
            'PLATE' => 25,  // Plate - common for connections
            'C' => 15,      // Channels
            'PIPE' => 12,   // Pipe
            'S' => 8,       // Standard beams
            'MC' => 8,      // Misc channels
            'HP' => 6,      // H-Piles
            'WT' => 6,      // Tees
            'BAR' => 10,    // Flat bars
        ];

        foreach ($itemsPerType as $type => $count) {
            if (!isset($materials[$type])) {
                continue;
            }

            $typeMaterials = $materials[$type]->toArray();
            if (empty($typeMaterials)) {
                continue;
            }

            for ($i = 0; $i < $count; $i++) {
                // Pick a random material of this type
                $material = $typeMaterials[array_rand($typeMaterials)];

                // Determine status (weighted: 70% free, 20% assigned, 10% committed)
                $statusRand = rand(1, 100);
                if ($statusRand <= 70) {
                    $status = 'free';
                    $projectId = null;
                } elseif ($statusRand <= 90) {
                    $status = 'assigned';
                    $projectId = !empty($projectIds) ? $projectIds[array_rand($projectIds)] : null;
                } else {
                    $status = 'committed';
                    $projectId = !empty($projectIds) ? $projectIds[array_rand($projectIds)] : null;
                }

                // Generate stock item data
                $lengths = $this->standardLengths[$type] ?? [240];
                $length = $lengths[array_rand($lengths)];

                // Add some variation to lengths (remnants, custom cuts)
                if (rand(1, 10) <= 2) {
                    $length = $length - rand(12, 60); // 1-5 feet shorter (remnant)
                    $isRemnant = true;
                } else {
                    $isRemnant = false;
                }

                $stockId = $this->generateStockId();
                $receiveDate = now()->subDays(rand(1, 180));

                $stockItem = [
                    'stock_id' => $stockId,
                    'material_id' => $material->id,
                    'type' => $type,
                    'size' => $material->size_imperial,
                    'grade' => $material->grade_code,
                    'length' => $length,
                    'quantity' => 1,
                    'status' => $status,
                    'reserved_project_id' => $projectId,
                    'stock_area' => $this->stockAreas[array_rand($this->stockAreas)],
                    'heat_number' => $this->heatNumbers[array_rand($this->heatNumbers)],
                    'po_number' => $this->poNumbers[array_rand($this->poNumbers)],
                    'country_of_origin' => $this->countries[array_rand($this->countries)],
                    'cost_per_unit' => round($material->price_per_lb * $material->unit_weight_lbs * ($length / 12), 2),
                    'receive_date' => $receiveDate->toDateString(),
                    'notes' => $isRemnant ? 'Remnant from previous job' : null,
                    'is_remnant' => $isRemnant,
                    'parent_stock_id' => null,
                    'created_at' => $receiveDate,
                    'updated_at' => $receiveDate,
                ];

                $stockItems[] = $stockItem;

                // Create corresponding stock movement for receiving
                $movements[] = [
                    'stock_item_id' => null, // Will be set after insert
                    'movement_type' => 'receive',
                    'quantity' => 1,
                    'from_status' => null,
                    'to_status' => 'free',
                    'from_area' => null,
                    'to_area' => $stockItem['stock_area'],
                    'reference_type' => 'po',
                    'reference_id' => null,
                    'notes' => "Received from {$stockItem['po_number']}",
                    'created_by' => 1,
                    'created_at' => $receiveDate,
                    'updated_at' => $receiveDate,
                ];

                // If assigned or committed, add movement for that too
                if ($status === 'assigned') {
                    $movements[] = [
                        'stock_item_id' => null,
                        'movement_type' => 'assign',
                        'quantity' => 1,
                        'from_status' => 'free',
                        'to_status' => 'assigned',
                        'from_area' => $stockItem['stock_area'],
                        'to_area' => $stockItem['stock_area'],
                        'reference_type' => 'project',
                        'reference_id' => $projectId,
                        'notes' => 'Assigned to project',
                        'created_by' => 1,
                        'created_at' => $receiveDate->addDays(rand(1, 30)),
                        'updated_at' => $receiveDate->addDays(rand(1, 30)),
                    ];
                } elseif ($status === 'committed') {
                    $movements[] = [
                        'stock_item_id' => null,
                        'movement_type' => 'assign',
                        'quantity' => 1,
                        'from_status' => 'free',
                        'to_status' => 'assigned',
                        'from_area' => $stockItem['stock_area'],
                        'to_area' => $stockItem['stock_area'],
                        'reference_type' => 'project',
                        'reference_id' => $projectId,
                        'notes' => 'Assigned to project',
                        'created_by' => 1,
                        'created_at' => $receiveDate->addDays(rand(1, 20)),
                        'updated_at' => $receiveDate->addDays(rand(1, 20)),
                    ];
                    $movements[] = [
                        'stock_item_id' => null,
                        'movement_type' => 'commit',
                        'quantity' => 1,
                        'from_status' => 'assigned',
                        'to_status' => 'committed',
                        'from_area' => $stockItem['stock_area'],
                        'to_area' => 'production',
                        'reference_type' => 'nesting',
                        'reference_id' => null,
                        'notes' => 'Committed to nesting',
                        'created_by' => 1,
                        'created_at' => $receiveDate->addDays(rand(21, 40)),
                        'updated_at' => $receiveDate->addDays(rand(21, 40)),
                    ];
                }

                $stockCount++;
            }
        }

        // Insert stock items in batches
        $batchSize = 50;
        $itemBatches = array_chunk($stockItems, $batchSize);

        foreach ($itemBatches as $batch) {
            DB::table('stock_items')->insert($batch);
        }

        // Get inserted stock item IDs and update movements
        $insertedItems = DB::table('stock_items')
            ->orderBy('id')
            ->pluck('id', 'stock_id')
            ->toArray();

        // Map movements to stock item IDs and insert
        $movementIndex = 0;
        foreach ($stockItems as $index => $item) {
            $stockItemId = $insertedItems[$item['stock_id']] ?? null;
            if (!$stockItemId) {
                continue;
            }

            // Find movements for this stock item (receive + optional assign/commit)
            $numMovements = 1; // receive
            if ($item['status'] === 'assigned') {
                $numMovements = 2;
            } elseif ($item['status'] === 'committed') {
                $numMovements = 3;
            }

            for ($m = 0; $m < $numMovements; $m++) {
                if (isset($movements[$movementIndex])) {
                    $movements[$movementIndex]['stock_item_id'] = $stockItemId;
                    $movementIndex++;
                }
            }
        }

        // Insert movements in batches
        $movementBatches = array_chunk($movements, $batchSize);
        foreach ($movementBatches as $batch) {
            // Filter out any movements without stock_item_id
            $validBatch = array_filter($batch, fn($m) => $m['stock_item_id'] !== null);
            if (!empty($validBatch)) {
                DB::table('stock_movements')->insert($validBatch);
            }
        }

        $this->command->info("Seeded {$stockCount} stock items with movement history");

        // Output summary by type
        $summary = collect($stockItems)->groupBy('type')->map->count();
        foreach ($summary as $type => $count) {
            $this->command->line("  - {$type}: {$count} items");
        }
    }

    /**
     * Generate a unique stock ID.
     */
    private function generateStockId(): string
    {
        return 'STK-' . strtoupper(Str::random(10));
    }
}
