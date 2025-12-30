<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Part;
use App\Models\Material;
use App\Services\Pricing\WeightCalculator;
use Mockery;

class WeightCalculatorTest extends TestCase
{
    protected WeightCalculator $calculator;

    protected function setUp(): void
    {
        // For a unit test, we can use a plain class if it doesn't need Laravel's container
        // But since we are using PHPUnit within Laravel context, standard setup applies.
        $this->calculator = new WeightCalculator();
    }

    public function test_it_calculates_lbs_correcty()
    {
        $material = Mockery::mock(Material::class);
        $material->shouldReceive('getAttribute')->with('unit_weight_lbs')->andReturn(10.0);
        $material->shouldReceive('getAttribute')->with('unit_weight_kg')->andReturn(14.88);

        $part = Mockery::mock(Part::class);
        $part->shouldReceive('getAttribute')->with('material')->andReturn($material);
        $part->shouldReceive('getAttribute')->with('length')->andReturn(2.0); // 2 feet
        $part->shouldReceive('getAttribute')->with('quantity')->andReturn(5);
        $part->shouldReceive('getAttribute')->with('weight_each_lbs')->andReturn(null);
        $part->shouldReceive('getAttribute')->with('weight_each_kg')->andReturn(null);

        $results = $this->calculator->calculatePartWeights($part);

        $this->assertEquals(20.0, $results['weight_each_lbs']); // 10 lbs/ft * 2 ft
        $this->assertEquals(100.0, $results['total_weight_lbs']); // 20 lbs * 5 qty
    }

    public function test_it_handles_metric_conversion()
    {
        $material = Mockery::mock(Material::class);
        $material->shouldReceive('getAttribute')->with('unit_weight_lbs')->andReturn(1.0);
        $material->shouldReceive('getAttribute')->with('unit_weight_kg')->andReturn(1.488);

        $part = Mockery::mock(Part::class);
        $part->shouldReceive('getAttribute')->with('material')->andReturn($material);
        $part->shouldReceive('getAttribute')->with('length')->andReturn(1.0); // 1 foot
        $part->shouldReceive('getAttribute')->with('quantity')->andReturn(1);
        $part->shouldReceive('getAttribute')->with('weight_each_lbs')->andReturn(null);
        $part->shouldReceive('getAttribute')->with('weight_each_kg')->andReturn(null);

        $results = $this->calculator->calculatePartWeights($part);

        // 1 foot * 0.3048 = 0.3048 meters
        // 1.488 kg/m * 0.3048 m = 0.4535 kg
        $this->assertEqualsWithDelta(0.4535, $results['weight_each_kg'], 0.0001);
    }
}
