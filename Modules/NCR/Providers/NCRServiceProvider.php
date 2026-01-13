<?php

namespace Modules\NCR\Providers;

use Illuminate\Support\ServiceProvider;

class NCRServiceProvider extends ServiceProvider
{
    protected string $name = 'NCR';

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function register(): void {}
}
