<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Inventory\Models\StockItem;
use Modules\NCR\Models\NCR;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| NCR Module - NCR Creation Tests
|--------------------------------------------------------------------------
*/

describe('NCR Creation', function () {
    beforeEach(function () {
        $this->user = User::factory()->admin()->create();
        $this->actingAs($this->user);
    });

    it('can create an NCR', function () {
        $ncr = NCR::create([
            'number' => 'NCR-001',
            'status' => 'created',
            'failure_reason' => 'Material defect discovered during fabrication',
            'reported_by' => $this->user->id,
        ]);

        expect($ncr)
            ->toBeInstanceOf(NCR::class)
            ->number->toBe('NCR-001')
            ->status->toBe('created')
            ->failure_reason->toContain('Material defect');

        $this->assertDatabaseHas('ncrs', [
            'number' => 'NCR-001',
        ]);
    });

    it('can link NCR to stock item', function () {
        $stockItem = StockItem::create([
            'stock_id' => 'STK-NCR001',
            'material_id' => 1,
            'type' => 'beam',
            'size' => 'W8x31',
            'grade' => 'A992',
            'length' => 240.0,
            'quantity' => 1,
            'status' => 'free',
        ]);

        $ncr = NCR::create([
            'number' => 'NCR-002',
            'status' => 'created',
            'failure_reason' => 'Incorrect dimensions',
            'stock_item_id' => $stockItem->id,
            'reported_by' => $this->user->id,
        ]);

        expect($ncr->stock_item_id)->toBe($stockItem->id);
    });

    it('records reporter information', function () {
        $ncr = NCR::create([
            'number' => 'NCR-003',
            'status' => 'created',
            'failure_reason' => 'Surface defect',
            'reported_by' => $this->user->id,
        ]);

        expect($ncr->reported_by)->toBe($this->user->id);
    });
});

/*
|--------------------------------------------------------------------------
| NCR Module - Status Workflow Tests
|--------------------------------------------------------------------------
*/

describe('NCR Status Workflow', function () {
    beforeEach(function () {
        $this->user = User::factory()->admin()->create();
        $this->actingAs($this->user);
    });

    it('NCR starts in created status', function () {
        $ncr = NCR::create([
            'number' => 'NCR-WF001',
            'status' => 'created',
            'failure_reason' => 'Test failure',
            'reported_by' => $this->user->id,
        ]);

        expect($ncr->status)->toBe('created');
    });

    it('can transition to reported status', function () {
        $ncr = NCR::create([
            'number' => 'NCR-WF002',
            'status' => 'created',
            'failure_reason' => 'Test failure',
            'reported_by' => $this->user->id,
        ]);

        $ncr->update(['status' => 'reported']);

        expect($ncr->refresh()->status)->toBe('reported');
    });

    it('can transition to dispositioned status with disposition', function () {
        $ncr = NCR::create([
            'number' => 'NCR-WF003',
            'status' => 'reported',
            'failure_reason' => 'Material damaged',
            'reported_by' => $this->user->id,
        ]);

        $ncr->update([
            'status' => 'dispositioned',
            'disposition' => 'scrap',
            'remediation_notes' => 'Material cannot be salvaged',
            'scrap_cost' => 500.00,
            'dispositioned_by' => $this->user->id,
            'dispositioned_at' => now(),
        ]);

        $ncr->refresh();

        expect($ncr)
            ->status->toBe('dispositioned')
            ->disposition->toBe('scrap')
            ->scrap_cost->toBe(500.0)
            ->dispositioned_by->toBe($this->user->id);
    });

    it('can transition to closed status', function () {
        $ncr = NCR::create([
            'number' => 'NCR-WF004',
            'status' => 'dispositioned',
            'disposition' => 'use_as_is',
            'failure_reason' => 'Minor cosmetic issue',
            'remediation_notes' => 'Engineering approved as-is',
            'reported_by' => $this->user->id,
            'dispositioned_by' => $this->user->id,
            'dispositioned_at' => now()->subDay(),
        ]);

        $ncr->update(['status' => 'closed']);

        expect($ncr->refresh()->status)->toBe('closed');
    });
});

/*
|--------------------------------------------------------------------------
| NCR Module - Disposition Tests
|--------------------------------------------------------------------------
*/

describe('NCR Disposition Types', function () {
    beforeEach(function () {
        $this->user = User::factory()->admin()->create();
        $this->actingAs($this->user);
    });

    it('can disposition as scrap', function () {
        $ncr = NCR::create([
            'number' => 'NCR-DISP001',
            'status' => 'reported',
            'failure_reason' => 'Major structural defect',
            'reported_by' => $this->user->id,
        ]);

        $ncr->update([
            'status' => 'dispositioned',
            'disposition' => 'scrap',
            'remediation_notes' => 'Cannot be repaired - must remake',
            'scrap_cost' => 1500.00,
            'dispositioned_by' => $this->user->id,
            'dispositioned_at' => now(),
        ]);

        expect($ncr->refresh())
            ->disposition->toBe('scrap')
            ->scrap_cost->toBe(1500.0);
    });

    it('can disposition as rework', function () {
        $ncr = NCR::create([
            'number' => 'NCR-DISP002',
            'status' => 'reported',
            'failure_reason' => 'Weld defect',
            'reported_by' => $this->user->id,
        ]);

        $ncr->update([
            'status' => 'dispositioned',
            'disposition' => 'rework',
            'remediation_notes' => 'Re-weld required at station 3',
            'rework_operation' => 'reweld',
            'dispositioned_by' => $this->user->id,
            'dispositioned_at' => now(),
        ]);

        expect($ncr->refresh())
            ->disposition->toBe('rework')
            ->rework_operation->toBe('reweld');
    });

    it('can disposition as use_as_is', function () {
        $ncr = NCR::create([
            'number' => 'NCR-DISP003',
            'status' => 'reported',
            'failure_reason' => 'Minor surface scratch',
            'reported_by' => $this->user->id,
        ]);

        $ncr->update([
            'status' => 'dispositioned',
            'disposition' => 'use_as_is',
            'remediation_notes' => 'Engineering approved - not structural',
            'dispositioned_by' => $this->user->id,
            'dispositioned_at' => now(),
        ]);

        expect($ncr->refresh())
            ->disposition->toBe('use_as_is');
    });
});

/*
|--------------------------------------------------------------------------
| NCR Module - Reporting Tests
|--------------------------------------------------------------------------
*/

describe('NCR Reporting', function () {
    beforeEach(function () {
        $this->user = User::factory()->admin()->create();
        $this->actingAs($this->user);
    });

    it('can query NCRs by status', function () {
        NCR::create([
            'number' => 'NCR-RPT001',
            'status' => 'created',
            'failure_reason' => 'Test 1',
            'reported_by' => $this->user->id,
        ]);

        NCR::create([
            'number' => 'NCR-RPT002',
            'status' => 'reported',
            'failure_reason' => 'Test 2',
            'reported_by' => $this->user->id,
        ]);

        NCR::create([
            'number' => 'NCR-RPT003',
            'status' => 'closed',
            'failure_reason' => 'Test 3',
            'reported_by' => $this->user->id,
        ]);

        expect(NCR::where('status', 'created')->count())->toBe(1);
        expect(NCR::where('status', 'reported')->count())->toBe(1);
        expect(NCR::where('status', 'closed')->count())->toBe(1);
    });

    it('can query NCRs by disposition', function () {
        NCR::create([
            'number' => 'NCR-RPT004',
            'status' => 'dispositioned',
            'disposition' => 'scrap',
            'failure_reason' => 'Scrap test',
            'reported_by' => $this->user->id,
        ]);

        NCR::create([
            'number' => 'NCR-RPT005',
            'status' => 'dispositioned',
            'disposition' => 'rework',
            'failure_reason' => 'Rework test',
            'reported_by' => $this->user->id,
        ]);

        NCR::create([
            'number' => 'NCR-RPT006',
            'status' => 'dispositioned',
            'disposition' => 'scrap',
            'failure_reason' => 'Another scrap',
            'reported_by' => $this->user->id,
        ]);

        expect(NCR::where('disposition', 'scrap')->count())->toBe(2);
        expect(NCR::where('disposition', 'rework')->count())->toBe(1);
    });

    it('can calculate total scrap costs', function () {
        NCR::create([
            'number' => 'NCR-COST001',
            'status' => 'dispositioned',
            'disposition' => 'scrap',
            'scrap_cost' => 500.00,
            'failure_reason' => 'Cost test 1',
            'reported_by' => $this->user->id,
        ]);

        NCR::create([
            'number' => 'NCR-COST002',
            'status' => 'dispositioned',
            'disposition' => 'scrap',
            'scrap_cost' => 750.00,
            'failure_reason' => 'Cost test 2',
            'reported_by' => $this->user->id,
        ]);

        $totalScrapCost = NCR::where('disposition', 'scrap')->sum('scrap_cost');

        expect($totalScrapCost)->toBe(1250.0);
    });
});

/*
|--------------------------------------------------------------------------
| NCR Module - Audit Trail Tests
|--------------------------------------------------------------------------
*/

describe('NCR Audit Trail', function () {
    beforeEach(function () {
        $this->user = User::factory()->admin()->create();
        $this->actingAs($this->user);
    });

    it('tracks who reported the NCR', function () {
        $ncr = NCR::create([
            'number' => 'NCR-AUDIT001',
            'status' => 'created',
            'failure_reason' => 'Audit test',
            'reported_by' => $this->user->id,
        ]);

        expect($ncr->reported_by)->toBe($this->user->id);
    });

    it('tracks who dispositioned the NCR', function () {
        $engineer = User::factory()->admin()->create(['name' => 'Quality Engineer']);

        $ncr = NCR::create([
            'number' => 'NCR-AUDIT002',
            'status' => 'reported',
            'failure_reason' => 'Audit test',
            'reported_by' => $this->user->id,
        ]);

        $this->actingAs($engineer);

        $ncr->update([
            'status' => 'dispositioned',
            'disposition' => 'use_as_is',
            'remediation_notes' => 'Approved',
            'dispositioned_by' => $engineer->id,
            'dispositioned_at' => now(),
        ]);

        expect($ncr->refresh())
            ->reported_by->toBe($this->user->id)
            ->dispositioned_by->toBe($engineer->id);
    });

    it('tracks disposition timestamp', function () {
        $ncr = NCR::create([
            'number' => 'NCR-AUDIT003',
            'status' => 'reported',
            'failure_reason' => 'Timestamp test',
            'reported_by' => $this->user->id,
        ]);

        $dispositionTime = now();

        $ncr->update([
            'status' => 'dispositioned',
            'disposition' => 'rework',
            'dispositioned_by' => $this->user->id,
            'dispositioned_at' => $dispositionTime,
        ]);

        expect($ncr->refresh()->dispositioned_at->format('Y-m-d H:i:s'))
            ->toBe($dispositionTime->format('Y-m-d H:i:s'));
    });
});
