<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use Modules\Inventory\Models\Grade;
use Modules\Inventory\Models\Material;
use Modules\Inventory\Models\StockItem;
use Modules\Inventory\Models\StockMovement;
use Modules\Inventory\Services\InventoryService;
use Tests\TestCase;

class InventoryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected InventoryService $service;

    protected User $user;

    protected Grade $grade;

    protected Material $material;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InventoryService();
        $this->user = User::factory()->create();
        Auth::login($this->user);

        $this->grade = Grade::create(['code' => 'A36', 'is_active' => true]);
        $this->material = Material::create([
            'type' => 'W',
            'size_imperial' => 'W12X26',
            'grade_id' => $this->grade->id,
            'unit_weight_lbs' => 26.0,
            'is_active' => true,
        ]);
    }

    protected function createStockItem(array $attributes = []): StockItem
    {
        return StockItem::create(array_merge([
            'stock_id' => 'STK-'.uniqid(),
            'material_id' => $this->material->id,
            'type' => 'W',
            'size' => 'W12X26',
            'grade' => 'A36',
            'length' => 20.0,
            'quantity' => 1,
            'status' => 'free',
            'stock_area' => 'yard_a',
        ], $attributes));
    }

    // ========== Transfer Tests ==========

    public function test_transfer_moves_stock_to_new_location(): void
    {
        $item = $this->createStockItem(['stock_area' => 'yard_a']);

        $movement = $this->service->transfer($item, 'warehouse', 'Transfer for testing');

        $item->refresh();
        $this->assertEquals('warehouse', $item->stock_area);
        $this->assertEquals('transfer', $movement->movement_type);
        $this->assertEquals('yard_a', $movement->from_area);
        $this->assertEquals('warehouse', $movement->to_area);
    }

    public function test_transfer_fails_when_destination_is_same(): void
    {
        $item = $this->createStockItem(['stock_area' => 'yard_a']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Stock item is already in the destination area.');

        $this->service->transfer($item, 'yard_a');
    }

    // ========== Assign Tests ==========

    public function test_assign_sets_project_and_status(): void
    {
        $item = $this->createStockItem(['status' => 'free']);
        $project = Project::factory()->create(['status' => 'active']);

        $movement = $this->service->assign($item, $project->id, 'Assigned for job');

        $item->refresh();
        $this->assertEquals('assigned', $item->status);
        $this->assertEquals($project->id, $item->reserved_project_id);
        $this->assertEquals('assign', $movement->movement_type);
        $this->assertEquals('project', $movement->reference_type);
        $this->assertEquals($project->id, $movement->reference_id);
    }

    public function test_assign_fails_from_used_status(): void
    {
        $item = $this->createStockItem(['status' => 'used']);
        $project = Project::factory()->create();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid status transition from 'used' to 'assigned'.");

        $this->service->assign($item, $project->id);
    }

    // ========== Release Tests ==========

    public function test_release_clears_project_assignment(): void
    {
        $project = Project::factory()->create();
        $item = $this->createStockItem([
            'status' => 'assigned',
            'reserved_project_id' => $project->id,
        ]);

        $movement = $this->service->release($item);

        $item->refresh();
        $this->assertEquals('free', $item->status);
        $this->assertNull($item->reserved_project_id);
        $this->assertEquals('release', $movement->movement_type);
    }

    // ========== Commit Tests ==========

    public function test_commit_changes_status_to_committed(): void
    {
        $item = $this->createStockItem(['status' => 'free']);

        $movement = $this->service->commit($item, 123, 'nesting');

        $item->refresh();
        $this->assertEquals('committed', $item->status);
        $this->assertEquals('commit', $movement->movement_type);
        $this->assertEquals('nesting', $movement->reference_type);
        $this->assertEquals(123, $movement->reference_id);
    }

    // ========== Use Tests ==========

    public function test_use_marks_item_as_used(): void
    {
        $item = $this->createStockItem(['status' => 'committed']);

        $movement = $this->service->use($item, 456, 'production');

        $item->refresh();
        $this->assertEquals('used', $item->status);
        $this->assertEquals('use', $movement->movement_type);
    }

    // ========== Return Tests ==========

    public function test_return_brings_item_back_to_free(): void
    {
        $item = $this->createStockItem(['status' => 'used']);

        $movement = $this->service->return($item);

        $item->refresh();
        $this->assertEquals('free', $item->status);
        $this->assertEquals('return', $movement->movement_type);
    }

    // ========== Adjust Tests ==========

    public function test_adjust_increases_quantity(): void
    {
        $item = $this->createStockItem(['quantity' => 5]);

        $movement = $this->service->adjust($item, 10, 'Physical count correction');

        $item->refresh();
        $this->assertEquals(10, $item->quantity);
        $this->assertEquals('adjustment_add', $movement->movement_type);
        $this->assertEquals(5, $movement->quantity);
    }

    public function test_adjust_decreases_quantity(): void
    {
        $item = $this->createStockItem(['quantity' => 10]);

        $movement = $this->service->adjust($item, 7, 'Physical count correction');

        $item->refresh();
        $this->assertEquals(7, $item->quantity);
        $this->assertEquals('adjustment_remove', $movement->movement_type);
        $this->assertEquals(3, $movement->quantity);
    }

    public function test_adjust_fails_when_no_change(): void
    {
        $item = $this->createStockItem(['quantity' => 5]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('New quantity is the same as current quantity.');

        $this->service->adjust($item, 5, 'No reason');
    }

    public function test_adjust_fails_with_negative_quantity(): void
    {
        $item = $this->createStockItem(['quantity' => 5]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity cannot be negative.');

        $this->service->adjust($item, -1, 'Bad reason');
    }

    // ========== Scrap Tests ==========

    public function test_scrap_marks_item_as_scrapped(): void
    {
        $item = $this->createStockItem(['status' => 'free']);

        $movement = $this->service->scrap($item, 'Damaged beyond repair');

        $item->refresh();
        $this->assertEquals('scrapped', $item->status);
        $this->assertEquals('scrap', $movement->movement_type);
        $this->assertStringContains('Damaged beyond repair', $movement->notes);
    }

    public function test_scrap_fails_when_already_scrapped(): void
    {
        $item = $this->createStockItem(['status' => 'scrapped']);

        $this->expectException(InvalidArgumentException::class);

        $this->service->scrap($item, 'Already scrapped');
    }

    // ========== Remnant Tests ==========

    public function test_create_remnant_splits_stock_item(): void
    {
        $item = $this->createStockItem(['length' => 20.0]);

        $remnant = $this->service->createRemnant($item, 5.0, 'Test remnant');

        $item->refresh();
        $this->assertEquals(15.0, $item->length);
        $this->assertEquals(5.0, $remnant->length);
        $this->assertTrue($remnant->is_remnant);
        $this->assertEquals($item->id, $remnant->parent_stock_id);
        $this->assertEquals('free', $remnant->status);
    }

    public function test_create_remnant_fails_with_invalid_length(): void
    {
        $item = $this->createStockItem(['length' => 20.0]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Remnant length must be less than parent item length.');

        $this->service->createRemnant($item, 25.0);
    }

    // ========== Bulk Operation Tests ==========

    public function test_bulk_transfer_moves_multiple_items(): void
    {
        $item1 = $this->createStockItem(['stock_area' => 'yard_a']);
        $item2 = $this->createStockItem(['stock_area' => 'yard_b']);

        $movements = $this->service->bulkTransfer([$item1->id, $item2->id], 'warehouse');

        $item1->refresh();
        $item2->refresh();

        $this->assertEquals('warehouse', $item1->stock_area);
        $this->assertEquals('warehouse', $item2->stock_area);
        $this->assertEquals(2, $movements->count());
    }

    public function test_bulk_assign_assigns_multiple_items(): void
    {
        $project = Project::factory()->create();
        $item1 = $this->createStockItem(['status' => 'free']);
        $item2 = $this->createStockItem(['status' => 'free']);

        $movements = $this->service->bulkAssign([$item1->id, $item2->id], $project->id);

        $item1->refresh();
        $item2->refresh();

        $this->assertEquals('assigned', $item1->status);
        $this->assertEquals('assigned', $item2->status);
        $this->assertEquals($project->id, $item1->reserved_project_id);
        $this->assertEquals(2, $movements->count());
    }

    public function test_bulk_release_releases_multiple_items(): void
    {
        $project = Project::factory()->create();
        $item1 = $this->createStockItem(['status' => 'assigned', 'reserved_project_id' => $project->id]);
        $item2 = $this->createStockItem(['status' => 'assigned', 'reserved_project_id' => $project->id]);

        $movements = $this->service->bulkRelease([$item1->id, $item2->id]);

        $item1->refresh();
        $item2->refresh();

        $this->assertEquals('free', $item1->status);
        $this->assertEquals('free', $item2->status);
        $this->assertEquals(2, $movements->count());
    }

    // ========== Valuation Tests ==========

    public function test_get_valuation_calculates_totals(): void
    {
        $this->createStockItem(['status' => 'free', 'quantity' => 5, 'cost_per_unit' => 100.00, 'length' => 20.0]);
        $this->createStockItem(['status' => 'assigned', 'quantity' => 3, 'cost_per_unit' => 150.00, 'length' => 15.0]);
        $this->createStockItem(['status' => 'scrapped', 'quantity' => 2, 'cost_per_unit' => 50.00, 'length' => 10.0]);

        $valuation = $this->service->getValuation();

        // Scrapped items should not be counted
        $this->assertEquals(8, $valuation['total_pieces']); // 5 + 3
        $this->assertEquals(950.00, $valuation['total_value']); // (5 * 100) + (3 * 150)
        $this->assertArrayHasKey('by_status', $valuation);
        $this->assertArrayHasKey('by_type', $valuation);
        $this->assertArrayHasKey('by_area', $valuation);
    }

    public function test_get_valuation_applies_filters(): void
    {
        $this->createStockItem(['status' => 'free', 'quantity' => 5, 'cost_per_unit' => 100.00, 'stock_area' => 'yard_a']);
        $this->createStockItem(['status' => 'free', 'quantity' => 3, 'cost_per_unit' => 150.00, 'stock_area' => 'warehouse']);

        $valuation = $this->service->getValuation(['stock_area' => 'yard_a']);

        $this->assertEquals(5, $valuation['total_pieces']);
        $this->assertEquals(500.00, $valuation['total_value']);
    }

    // ========== Status Transition Tests ==========

    public function test_can_transition_validates_allowed_transitions(): void
    {
        $this->assertTrue($this->service->canTransition('free', 'assigned'));
        $this->assertTrue($this->service->canTransition('free', 'committed'));
        $this->assertTrue($this->service->canTransition('free', 'used'));
        $this->assertTrue($this->service->canTransition('free', 'scrapped'));

        $this->assertTrue($this->service->canTransition('assigned', 'free'));
        $this->assertTrue($this->service->canTransition('committed', 'used'));
        $this->assertTrue($this->service->canTransition('used', 'free'));

        $this->assertFalse($this->service->canTransition('scrapped', 'free'));
        $this->assertFalse($this->service->canTransition('scrapped', 'assigned'));
    }

    public function test_get_valid_transitions_returns_correct_list(): void
    {
        $transitions = $this->service->getValidTransitions('free');

        $this->assertContains('assigned', $transitions);
        $this->assertContains('committed', $transitions);
        $this->assertContains('used', $transitions);
        $this->assertContains('scrapped', $transitions);

        $scrapTransitions = $this->service->getValidTransitions('scrapped');
        $this->assertEmpty($scrapTransitions);
    }

    // ========== Movement Audit Tests ==========

    public function test_movement_records_user_who_made_change(): void
    {
        $item = $this->createStockItem();

        $movement = $this->service->transfer($item, 'warehouse');

        $this->assertEquals($this->user->id, $movement->created_by);
    }

    public function test_movement_records_status_changes(): void
    {
        $item = $this->createStockItem(['status' => 'free']);

        $movement = $this->service->commit($item);

        $this->assertEquals('free', $movement->from_status);
        $this->assertEquals('committed', $movement->to_status);
    }

    // ========== Helper assertion ==========

    protected function assertStringContains(string $needle, ?string $haystack): void
    {
        $this->assertNotNull($haystack);
        $this->assertStringContainsString($needle, $haystack);
    }
}
