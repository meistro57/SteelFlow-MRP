<?php

namespace Modules\UPF\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\UPF\Models\MaterialType;
use Modules\UPF\Models\UpfPrice;
use Modules\UPF\Services\PricingService;
use Tests\TestCase;

class PricingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PricingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PricingService::class);
    }

    /**
     * Test price calculation for weight-based pricing (LB)
     */
    public function test_it_calculates_lbs_correctly()
    {
        $type = MaterialType::create(['type' => 'PLATE', 'title' => 'Plate', 'pkey' => 'M1']);
        UpfPrice::create([
            'material_type_id' => $type->id,
            'type' => 'PLATE',
            'size' => '1/2',
            'unit_price' => 0.50,
            'price_unit' => 'LB',
            'pkey' => 'U1',
            'filekey' => 1,
            'orderkey' => 1,
        ]);

        $cost = $this->service->calculateCost('PLATE', '1/2', null, 1, 0, 100);
        $this->assertEquals(50.0, $cost); // 100 lbs * 0.50
    }

    /**
     * Test calculation for quantity-based pricing (EA)
     */
    public function test_it_calculates_each_correctly()
    {
        $type = MaterialType::create(['type' => 'BOLT', 'title' => 'Bolt', 'pkey' => 'M2']);
        UpfPrice::create([
            'material_type_id' => $type->id,
            'type' => 'BOLT',
            'size' => '3/4 X 2',
            'unit_price' => 2.50,
            'price_unit' => 'EA',
            'pkey' => 'U2',
            'filekey' => 2,
            'orderkey' => 2,
        ]);

        $cost = $this->service->calculateCost('BOLT', '3/4 X 2', null, 10, 0, 0);
        $this->assertEquals(25.0, $cost); // 10 EA * 2.50
    }

    /**
     * Test that minimum charge is applied if calculated cost is lower
     */
    public function test_it_applies_minimum_charge()
    {
        $type = MaterialType::create(['type' => 'PLATE', 'title' => 'Plate', 'pkey' => 'M1']);
        UpfPrice::create([
            'material_type_id' => $type->id,
            'type' => 'PLATE',
            'size' => '1/2',
            'unit_price' => 0.50,
            'price_unit' => 'LB',
            'minimum_charge' => 25.00,
            'pkey' => 'U1',
            'filekey' => 1,
            'orderkey' => 1,
        ]);

        $cost = $this->service->calculateCost('PLATE', '1/2', null, 1, 0, 10);
        $this->assertEquals(25.0, $cost); // 5.0 scaled to 25.0 min
    }
}
