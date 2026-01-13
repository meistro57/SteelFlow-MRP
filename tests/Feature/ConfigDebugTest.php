<?php

namespace Tests\Feature;

use Tests\TestCase;

class ConfigDebugTest extends TestCase
{
    public function test_config_helper_works()
    {
        $this->assertEquals('testing', config('app.env'));
    }

    public function test_config_resolution_works()
    {
        $this->assertInstanceOf(\Illuminate\Config\Repository::class, app('config'));
    }
}
