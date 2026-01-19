<?php

namespace Tests\Unit;

use App\Models\Part;
use App\Services\Pricing\WeightCalculator;
use Modules\Inventory\Models\Material;
use Tests\TestCase;

class WeightCalculatorTest extends TestCase
{
    protected WeightCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new WeightCalculator();
    }

    public function test_it_calculates_lbs_correctly()
    {
        $material = new Material([
            'unit_weight_lbs' => 10.0,
            'unit_weight_kg' => 14.88,
        ]);

        $part = new Part([
            // WE NEED 2 FEET
            // 24.0 inches / 12 = 2.0 feet
            'length' => 24.0,
            'quantity' => 5,
        ]);

        $part->setRelation('material', $material);

        $results = $this->calculator->calculatePartWeights($part);

        $this->assertEquals(20.0, $results['weight_each_lbs']); // 10 lbs/ft * 2 ft = 20
        $this->assertEquals(100.0, $results['total_weight_lbs']); // 20 lbs * 5 qty = 100
    }

    public function test_it_handles_metric_conversion()
    {
        $material = new Material([
            'unit_weight_lbs' => 1.0,
            'unit_weight_kg' => 1.488,
        ]);

        $part = new Part([
            // WE NEED 1 FOOT (to match the metric math expectation)
            // 12.0 inches = 1 foot = 0.3048 meters
            'length' => 12.0,
            'quantity' => 1,
        ]);

        $part->setRelation('material', $material);

        $results = $this->calculator->calculatePartWeights($part);

        // 1.488 kg/m * 0.3048 m = 0.4535 kg
        $this->assertEqualsWithDelta(0.4535, $results['weight_each_kg'], 0.0001);
    }
}
