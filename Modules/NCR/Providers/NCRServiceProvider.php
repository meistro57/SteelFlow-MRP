<?php

namespace Modules\NCR\Providers;

use Illuminate\Support\ServiceProvider;

class NCRServiceProvider extends ServiceProvider
{
    protected string $name = 'NCR';

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));
    }

    public function register(): void
    {
        //
    }
}
