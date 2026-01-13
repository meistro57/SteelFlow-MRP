<?php

namespace Modules\Shipping\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Traits\PathNamespace;

class ShippingServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Shipping';

    protected string $nameLower = 'shipping';

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
