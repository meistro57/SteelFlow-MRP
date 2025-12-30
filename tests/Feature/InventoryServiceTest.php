<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Models\Vendor;
use App\Models\Material;
use App\Models\Grade;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected InventoryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InventoryService();
    }

    public function test_receiving_line_creates_stock_items()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $grade = Grade::create(['code' => 'A36']);
        $material = Material::create([
            'type' => 'W', 
            'size_imperial' => 'W12X26', 
            'grade_id' => $grade->id,
            'unit_weight_lbs' => 26.0
        ]);

        $vendor = Vendor::create(['name' => 'Steel Corp', 'code' => 'V-001']);
        $po = PurchaseOrder::create(['po_number' => 'PO-100', 'vendor_id' => $vendor->id]);
        $line = $po->lines()->create([
            'line_number' => 1,
            'material_id' => $material->id,
            'type' => 'W',
            'size' => 'W12X26',
            'grade' => 'A36',
            'length' => 240, // 20ft
            'quantity' => 10,
        ]);

        $this->service->receiveLine($line, [
            'quantity' => 2,
            'receive_date' => now(),
            'heat_number' => 'HEAT-123',
        ]);

        $this->assertEquals(2, $line->fresh()->quantity_received);
        $this->assertDatabaseCount('stock_items', 2);
        $this->assertDatabaseHas('stock_items', [
            'heat_number' => 'HEAT-123',
            'po_number' => 'PO-100'
        ]);
    }
}
