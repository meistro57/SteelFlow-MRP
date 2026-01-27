<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Modules\Inventory\Models\Grade;
use Modules\Inventory\Models\Material;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        // Must set config BEFORE parent::setUp() for some services in Laravel 11
        $tmpViews = sys_get_temp_dir().'/sf_views_'.posix_getuid();
        if (! is_dir($tmpViews)) {
            @mkdir($tmpViews, 0777, true);
        }
        putenv("VIEW_COMPILED_PATH=$tmpViews");

        parent::setUp();

        config([
            // Ensure tests are not affected by cached non-testing config.
            'app.env' => 'testing',
            'cache.default' => 'array',
            'session.driver' => 'array',
            'logging.default' => 'stderr',
            'view.compiled' => $tmpViews,
        ]);

        // Many tests assume material_id=1 exists. Create a minimal reference set.
        if (! Grade::query()->exists()) {
            $grade = Grade::query()->create([
                'code' => 'A36',
                'description' => 'ASTM A36',
                'is_active' => true,
            ]);

            Material::query()->create([
                'type' => 'plate',
                'size_imperial' => '1/2 x 48 x 120',
                'size_metric' => null,
                'grade_id' => $grade->id,
                'unit_weight_lbs' => 1,
                'unit_weight_kg' => 0.4536,
                'price_per_lb' => null,
                'price_per_kg' => null,
                'surface_area_sqft' => null,
                'is_active' => true,
                'sort_order' => 1,
            ]);
        }
    }
}
