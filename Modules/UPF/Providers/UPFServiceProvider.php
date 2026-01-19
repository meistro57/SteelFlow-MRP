<?php

namespace Modules\UPF\Providers;

use Illuminate\Support\ServiceProvider;

class UPFServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\UPF\Console\Commands\ImportUpfCommand::class,
            ]);
        }
    }

    public function register(): void
    {
        // Auto-resolution handles the services
    }
}
