<?php

namespace Modules\Nesting\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Traits\PathNamespace;

class NestingServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Nesting';

    protected string $nameLower = 'nesting';

    public function boot(): void
    {
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/config.php', $this->nameLower,
        );
    }

    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->nameLower);
        $sourcePath = __DIR__.'/../resources/views';

        if (is_dir($sourcePath)) {
            $this->loadViewsFrom($sourcePath, $this->nameLower);
        }

        Blade::componentNamespace('Modules\\'.$this->name.'\\View\\Components', $this->nameLower);
    }
}
