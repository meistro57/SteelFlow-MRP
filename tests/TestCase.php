<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

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
            'cache.default' => 'array',
            'session.driver' => 'array',
            'logging.default' => 'stderr',
            'view.compiled' => $tmpViews,
        ]);
    }
}
