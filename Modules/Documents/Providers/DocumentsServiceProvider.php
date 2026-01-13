<?php

namespace Modules\Documents\Providers;

use Illuminate\Support\ServiceProvider;

class DocumentsServiceProvider extends ServiceProvider
{
    protected string $name = 'Documents';

    protected string $nameLower = 'documents';

    public function boot(): void
    {
        $this->registerConfig();
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function register(): void
    {
        // Register services here
    }

    protected function registerConfig(): void
    {
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path($this->nameLower.'.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../config/config.php', $this->nameLower,
        );
    }
}
