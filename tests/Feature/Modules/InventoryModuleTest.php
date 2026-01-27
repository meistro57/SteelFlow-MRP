<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Inventory\Models\Grade;
use Modules\Inventory\Models\Material;
use Modules\Inventory\Models\StockItem;
use Modules\Inventory\Models\StockMovement;
use Modules\Inventory\Services\InventoryService;

uses(RefreshDatabase::class);

function createInventoryTestMaterial(): int
{
    $grade = Grade::query()->firstOrCreate(
        ['code' => 'A36'],
        [
            'description' => 'ASTM A36',
            'is_active' => true,
        ],
    );

    $material = Material::query()->firstOrCreate(
        [
            'type' => 'plate',
            'size_imperial' => '1/2 x 48 x 120',
            'grade_id' => $grade->id,
        ],
        [
            'size_metric' => null,
            'unit_weight_lbs' => 1,
            'unit_weight_kg' => 0.4536,
            'price_per_lb' => null,
            'price_per_kg' => null,
            'surface_area_sqft' => null,
            'is_active' => true,
            'sort_order' => 1,
        ],
    );

    return $material->id;
}

/*
|--------------------------------------------------------------------------
| Inventory Service - Status Transitions Tests
|--------------------------------------------------------------------------
*/

describe('InventoryService Status Transitions', function () {
    beforeEach(function () {
        $this->service = app(InventoryService::class);
        $this->user = User::factory()->admin()->create();
        $this->actingAs($this->user);
        $this->materialId = createInventoryTestMaterial();
    });

    it('allows valid status transitions', function () {
        // free -> assigned
        expect($this->service->canTransition('free', 'assigned'))->toBeTrue();
        expect($this->service->canTransition('free', 'committed'))->toBeTrue();
        expect($this->service->canTransition('free', 'used'))->toBeTrue();
        expect($this->service->canTransition('free', 'scrapped'))->toBeTrue();

        // assigned -> transitions
        expect($this->service->canTransition('assigned', 'free'))->toBeTrue();
        expect($this->service->canTransition('assigned', 'committed'))->toBeTrue();

        // committed -> transitions
        expect($this->service->canTransition('committed', 'used'))->toBeTrue();

        // used -> transitions
        expect($this->service->canTransition('used', 'free'))->toBeTrue();

        // scrapped is terminal
        expect($this->service->canTransition('scrapped', 'free'))->toBeFalse();
    });

    it('rejects invalid status transitions', function () {
        // Cannot transition from scrapped (terminal)
        expect($this->service->canTransition('scrapped', 'free'))->toBeFalse();
        expect($this->service->canTransition('scrapped', 'assigned'))->toBeFalse();
    });

    it('returns valid transitions for a status', function () {
        $freeTransitions = $this->service->getValidTransitions('free');

        expect($freeTransitions)
            ->toBeArray()
            ->toContain('assigned')
            ->toContain('committed')
            ->toContain('used')
            ->toContain('scrapped');

        $scrappedTransitions = $this->service->getValidTransitions('scrapped');
        expect($scrappedTransitions)->toBeEmpty();
    });
});

/*
|--------------------------------------------------------------------------
| Inventory Service - Stock Operations Tests
|--------------------------------------------------------------------------
*/

describe('InventoryService Stock Operations', function () {
    beforeEach(function () {
        $this->service = app(InventoryService::class);
        $this->user = User::factory()->admin()->create();
        $this->actingAs($this->user);
        $this->materialId = createInventoryTestMaterial();
    });

    it('can assign stock to a project', function () {
        $project = Project::factory()->create();
        $stockItem = StockItem::create([
            'stock_id' => 'STK-TEST001',
            'material_id' => $this->materialId,
            'type' => 'beam',
            'size' => 'W8x31',
            'grade' => 'A992',
            'length' => 240.0,
            'quantity' => 1,
            'status' => 'free',
        ]);

        $movement = $this->service->assign($stockItem, $project->id, 'Test assignment');

        expect($stockItem->refresh())
            ->status->toBe('assigned')
            ->reserved_project_id->toBe($project->id);

        expect($movement)
            ->toBeInstanceOf(StockMovement::class)
            ->movement_type->toBe('assign')
            ->from_status->toBe('free')
            ->to_status->toBe('assigned');
    });

    it('can release stock from a project', function () {
        $project = Project::factory()->create();
        $stockItem = StockItem::create([
            'stock_id' => 'STK-TEST002',
            'material_id' => $this->materialId,
            'type' => 'beam',
            'size' => 'W8x31',
            'grade' => 'A992',
            'length' => 240.0,
            'quantity' => 1,
            'status' => 'assigned',
            'reserved_project_id' => $project->id,
        ]);

        $movement = $this->service->release($stockItem, 'Test release');

        expect($stockItem->refresh())
            ->status->toBe('free')
            ->reserved_project_id->toBeNull();

        expect($movement)
            ->movement_type->toBe('release')
            ->from_status->toBe('assigned')
            ->to_status->toBe('free');
    });

    it('can commit stock to production', function () {
        $stockItem = StockItem::create([
            'stock_id' => 'STK-TEST003',
            'material_id' => $this->materialId,
            'type' => 'plate',
            'size' => '1/2 x 48 x 120',
            'grade' => 'A36',
            'length' => 120.0,
            'quantity' => 1,
            'status' => 'free',
        ]);

        $movement = $this->service->commit($stockItem, 1, 'nesting', 'For nesting batch');

        expect($stockItem->refresh()->status)->toBe('committed');
        expect($movement->movement_type)->toBe('commit');
    });

    it('can mark stock as used', function () {
        $stockItem = StockItem::create([
            'stock_id' => 'STK-TEST004',
            'material_id' => $this->materialId,
            'type' => 'angle',
            'size' => 'L4x4x1/4',
            'grade' => 'A36',
            'length' => 180.0,
            'quantity' => 1,
            'status' => 'committed',
        ]);

        $movement = $this->service->use($stockItem, 1, 'production_item', 'Used in production');

        expect($stockItem->refresh()->status)->toBe('used');
        expect($movement->movement_type)->toBe('use');
    });

    it('can return used stock to free', function () {
        $stockItem = StockItem::create([
            'stock_id' => 'STK-TEST005',
            'material_id' => $this->materialId,
            'type' => 'channel',
            'size' => 'C6x8.2',
            'grade' => 'A36',
            'length' => 240.0,
            'quantity' => 1,
            'status' => 'used',
        ]);

        $movement = $this->service->return($stockItem, 'Returning unused portion');

        expect($stockItem->refresh()->status)->toBe('free');
        expect($movement->movement_type)->toBe('return');
    });

    it('can scrap stock', function () {
        $stockItem = StockItem::create([
            'stock_id' => 'STK-TEST006',
            'material_id' => $this->materialId,
            'type' => 'bar',
            'size' => '1" Round',
            'grade' => 'A36',
            'length' => 120.0,
            'quantity' => 1,
            'status' => 'free',
        ]);

        $movement = $this->service->scrap($stockItem, 'Damaged during handling', 'NCR #001');

        expect($stockItem->refresh()->status)->toBe('scrapped');
        expect($movement->movement_type)->toBe('scrap');
        expect($movement->notes)->toContain('Damaged during handling');
    });

    it('throws exception for invalid status transition', function () {
        $stockItem = StockItem::create([
            'stock_id' => 'STK-TEST007',
            'material_id' => $this->materialId,
            'type' => 'beam',
            'size' => 'W10x22',
            'grade' => 'A992',
            'length' => 240.0,
            'quantity' => 1,
            'status' => 'scrapped',
        ]);

        $this->service->release($stockItem, 'Invalid transition');
    })->throws(InvalidArgumentException::class);
});

/*
|--------------------------------------------------------------------------
| Inventory Service - Transfer Tests
|--------------------------------------------------------------------------
*/

describe('InventoryService Transfer', function () {
    beforeEach(function () {
        $this->service = app(InventoryService::class);
        $this->user = User::factory()->admin()->create();
        $this->actingAs($this->user);
        $this->materialId = createInventoryTestMaterial();
    });

    it('can transfer stock to a different area', function () {
        $stockItem = StockItem::create([
            'stock_id' => 'STK-TRANS001',
            'material_id' => $this->materialId,
            'type' => 'beam',
            'size' => 'W8x31',
            'grade' => 'A992',
            'length' => 240.0,
            'quantity' => 1,
            'status' => 'free',
            'stock_area' => 'Bay 1',
        ]);

        $movement = $this->service->transfer($stockItem, 'Bay 2', 'Relocating for project');

        expect($stockItem->refresh()->stock_area)->toBe('Bay 2');
        expect($movement)
            ->movement_type->toBe('transfer')
            ->from_area->toBe('Bay 1')
            ->to_area->toBe('Bay 2');
    });

    it('throws exception when transferring to same area', function () {
        $stockItem = StockItem::create([
            'stock_id' => 'STK-TRANS002',
            'material_id' => $this->materialId,
            'type' => 'plate',
            'size' => '1/2 x 48 x 120',
            'grade' => 'A36',
            'length' => 120.0,
            'quantity' => 1,
            'status' => 'free',
            'stock_area' => 'Bay 1',
        ]);

        $this->service->transfer($stockItem, 'Bay 1', 'Same area transfer');
    })->throws(InvalidArgumentException::class);
});

/*
|--------------------------------------------------------------------------
| Inventory Service - Adjustment Tests
|--------------------------------------------------------------------------
*/

describe('InventoryService Adjustments', function () {
    beforeEach(function () {
        $this->service = app(InventoryService::class);
        $this->user = User::factory()->admin()->create();
        $this->actingAs($this->user);
        $this->materialId = createInventoryTestMaterial();
    });

    it('can adjust quantity upward', function () {
        $stockItem = StockItem::create([
            'stock_id' => 'STK-ADJ001',
            'material_id' => $this->materialId,
            'type' => 'bar',
            'size' => '1" Round',
            'grade' => 'A36',
            'length' => 120.0,
            'quantity' => 5,
            'status' => 'free',
        ]);

        $movement = $this->service->adjust($stockItem, 8, 'Found additional stock', 'Physical count');

        expect($stockItem->refresh()->quantity)->toBe(8);
        expect($movement)
            ->movement_type->toBe('adjustment_add')
            ->quantity->toBe(3);
    });

    it('can adjust quantity downward', function () {
        $stockItem = StockItem::create([
            'stock_id' => 'STK-ADJ002',
            'material_id' => $this->materialId,
            'type' => 'angle',
            'size' => 'L4x4x1/4',
            'grade' => 'A36',
            'length' => 180.0,
            'quantity' => 10,
            'status' => 'free',
        ]);

        $movement = $this->service->adjust($stockItem, 7, 'Cycle count variance', 'Adjusted per count');

        expect($stockItem->refresh()->quantity)->toBe(7);
        expect($movement)
            ->movement_type->toBe('adjustment_remove')
            ->quantity->toBe(3);
    });

    it('throws exception for same quantity adjustment', function () {
        $stockItem = StockItem::create([
            'stock_id' => 'STK-ADJ003',
            'material_id' => $this->materialId,
            'type' => 'channel',
            'size' => 'C6x8.2',
            'grade' => 'A36',
            'length' => 240.0,
            'quantity' => 5,
            'status' => 'free',
        ]);

        $this->service->adjust($stockItem, 5, 'No change', 'Test');
    })->throws(InvalidArgumentException::class);

    it('throws exception for negative quantity', function () {
        $stockItem = StockItem::create([
            'stock_id' => 'STK-ADJ004',
            'material_id' => $this->materialId,
            'type' => 'beam',
            'size' => 'W8x31',
            'grade' => 'A992',
            'length' => 240.0,
            'quantity' => 5,
            'status' => 'free',
        ]);

        $this->service->adjust($stockItem, -1, 'Invalid quantity', 'Test');
    })->throws(InvalidArgumentException::class);
});

/*
|--------------------------------------------------------------------------
| Inventory Service - Remnant Tests
|--------------------------------------------------------------------------
*/

describe('InventoryService Remnants', function () {
    beforeEach(function () {
        $this->service = app(InventoryService::class);
        $this->user = User::factory()->admin()->create();
        $this->actingAs($this->user);
        $this->materialId = createInventoryTestMaterial();
    });

    it('can create a remnant from stock', function () {
        $parentItem = StockItem::create([
            'stock_id' => 'STK-REM001',
            'material_id' => $this->materialId,
            'type' => 'beam',
            'size' => 'W8x31',
            'grade' => 'A992',
            'length' => 240.0,
            'quantity' => 1,
            'status' => 'free',
            'stock_area' => 'Bay 1',
            'heat_number' => 'HT12345',
        ]);

        $remnant = $this->service->createRemnant($parentItem, 60.0, 'Split for project');

        expect($remnant)
            ->toBeInstanceOf(StockItem::class)
            ->length->toBe(60.0)
            ->is_remnant->toBeTrue()
            ->parent_stock_id->toBe($parentItem->id)
            ->status->toBe('free')
            ->heat_number->toBe('HT12345');

        expect((float) $parentItem->refresh()->length)->toBe(180.0);
    });

    it('throws exception for invalid remnant length', function () {
        $parentItem = StockItem::create([
            'stock_id' => 'STK-REM002',
            'material_id' => $this->materialId,
            'type' => 'plate',
            'size' => '1/2 x 48 x 120',
            'grade' => 'A36',
            'length' => 100.0,
            'quantity' => 1,
            'status' => 'free',
        ]);

        $this->service->createRemnant($parentItem, 150.0, 'Invalid length');
    })->throws(InvalidArgumentException::class);
});

/*
|--------------------------------------------------------------------------
| Inventory Service - Bulk Operations Tests
|--------------------------------------------------------------------------
*/

describe('InventoryService Bulk Operations', function () {
    beforeEach(function () {
        $this->service = app(InventoryService::class);
        $this->user = User::factory()->admin()->create();
        $this->actingAs($this->user);
        $this->materialId = createInventoryTestMaterial();
    });

    it('can bulk transfer multiple items', function () {
        $items = collect([
            StockItem::create([
                'stock_id' => 'STK-BULK001',
                'material_id' => $this->materialId,
                'type' => 'beam',
                'size' => 'W8x31',
                'grade' => 'A992',
                'length' => 240.0,
                'quantity' => 1,
                'status' => 'free',
                'stock_area' => 'Bay 1',
            ]),
            StockItem::create([
                'stock_id' => 'STK-BULK002',
                'material_id' => $this->materialId,
                'type' => 'beam',
                'size' => 'W10x22',
                'grade' => 'A992',
                'length' => 240.0,
                'quantity' => 1,
                'status' => 'free',
                'stock_area' => 'Bay 1',
            ]),
        ]);

        $movements = $this->service->bulkTransfer($items->pluck('id')->toArray(), 'Bay 2', 'Bulk transfer');

        expect($movements)->toHaveCount(2);

        $items->each(fn ($item) => expect($item->refresh()->stock_area)->toBe('Bay 2'));
    });

    it('can bulk assign items to a project', function () {
        $project = Project::factory()->create();

        $items = collect([
            StockItem::create([
                'stock_id' => 'STK-BULK003',
                'material_id' => $this->materialId,
                'type' => 'plate',
                'size' => '1/2 x 48 x 120',
                'grade' => 'A36',
                'length' => 120.0,
                'quantity' => 1,
                'status' => 'free',
            ]),
            StockItem::create([
                'stock_id' => 'STK-BULK004',
                'material_id' => $this->materialId,
                'type' => 'plate',
                'size' => '3/4 x 48 x 120',
                'grade' => 'A36',
                'length' => 120.0,
                'quantity' => 1,
                'status' => 'free',
            ]),
        ]);

        $movements = $this->service->bulkAssign($items->pluck('id')->toArray(), $project->id, 'Bulk assignment');

        expect($movements)->toHaveCount(2);

        $items->each(function ($item) use ($project) {
            $item->refresh();
            expect($item->status)->toBe('assigned');
            expect($item->reserved_project_id)->toBe($project->id);
        });
    });
});

/*
|--------------------------------------------------------------------------
| Inventory Service - Valuation Tests
|--------------------------------------------------------------------------
*/

describe('InventoryService Valuation', function () {
    beforeEach(function () {
        $this->service = app(InventoryService::class);
        $this->user = User::factory()->admin()->create();
        $this->actingAs($this->user);
        $this->materialId = createInventoryTestMaterial();
    });

    it('calculates total valuation correctly', function () {
        StockItem::create([
            'stock_id' => 'STK-VAL001',
            'material_id' => $this->materialId,
            'type' => 'beam',
            'size' => 'W8x31',
            'grade' => 'A992',
            'length' => 240.0,
            'quantity' => 2,
            'status' => 'free',
            'cost_per_unit' => 100.00,
        ]);

        StockItem::create([
            'stock_id' => 'STK-VAL002',
            'material_id' => $this->materialId,
            'type' => 'plate',
            'size' => '1/2 x 48 x 120',
            'grade' => 'A36',
            'length' => 120.0,
            'quantity' => 1,
            'status' => 'assigned',
            'cost_per_unit' => 250.00,
        ]);

        $valuation = $this->service->getValuation();

        expect($valuation)
            ->toHaveKey('total_pieces')
            ->toHaveKey('total_value')
            ->toHaveKey('by_status')
            ->toHaveKey('by_type');

        expect($valuation['total_pieces'])->toBe(3);
        expect($valuation['total_value'])->toBe(450.0); // (2 * 100) + (1 * 250)
    });

    it('filters valuation by status', function () {
        StockItem::create([
            'stock_id' => 'STK-VAL003',
            'material_id' => $this->materialId,
            'type' => 'beam',
            'size' => 'W8x31',
            'grade' => 'A992',
            'length' => 240.0,
            'quantity' => 1,
            'status' => 'free',
            'cost_per_unit' => 100.00,
        ]);

        StockItem::create([
            'stock_id' => 'STK-VAL004',
            'material_id' => $this->materialId,
            'type' => 'plate',
            'size' => '1/2 x 48 x 120',
            'grade' => 'A36',
            'length' => 120.0,
            'quantity' => 1,
            'status' => 'assigned',
            'cost_per_unit' => 250.00,
        ]);

        $valuation = $this->service->getValuation(['status' => 'free']);

        expect($valuation['total_pieces'])->toBe(1);
        expect($valuation['total_value'])->toBe(100.0);
    });
});

/*
|--------------------------------------------------------------------------
| Movement Audit Trail Tests
|--------------------------------------------------------------------------
*/

describe('Stock Movement Audit Trail', function () {
    beforeEach(function () {
        $this->service = app(InventoryService::class);
        $this->user = User::factory()->admin()->create();
        $this->actingAs($this->user);
        $this->materialId = createInventoryTestMaterial();
    });

    it('creates movement record for each operation', function () {
        $stockItem = StockItem::create([
            'stock_id' => 'STK-AUDIT001',
            'material_id' => $this->materialId,
            'type' => 'beam',
            'size' => 'W8x31',
            'grade' => 'A992',
            'length' => 240.0,
            'quantity' => 1,
            'status' => 'free',
        ]);

        $project = Project::factory()->create();

        // Assign
        $this->service->assign($stockItem, $project->id);
        // Commit
        $this->service->commit($stockItem->refresh());
        // Use
        $this->service->use($stockItem->refresh());

        $movements = $stockItem->movements()->orderBy('created_at')->get();

        expect($movements)->toHaveCount(3);
        expect($movements[0]->movement_type)->toBe('assign');
        expect($movements[1]->movement_type)->toBe('commit');
        expect($movements[2]->movement_type)->toBe('use');
    });

    it('records user who made the movement', function () {
        $stockItem = StockItem::create([
            'stock_id' => 'STK-AUDIT002',
            'material_id' => $this->materialId,
            'type' => 'plate',
            'size' => '1/2 x 48 x 120',
            'grade' => 'A36',
            'length' => 120.0,
            'quantity' => 1,
            'status' => 'free',
            'stock_area' => 'Bay 1',
        ]);

        $movement = $this->service->transfer($stockItem, 'Bay 2', 'Test transfer');

        expect($movement->created_by)->toBe($this->user->id);
    });
});
