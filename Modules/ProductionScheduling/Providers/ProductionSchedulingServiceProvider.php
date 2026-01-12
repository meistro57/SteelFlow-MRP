<?php

namespace Modules\ProductionScheduling\Providers;

use Illuminate\Support\ServiceProvider;

class ProductionSchedulingServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
