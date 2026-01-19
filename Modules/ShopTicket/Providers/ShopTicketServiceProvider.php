<?php

namespace Modules\ShopTicket\Providers;

use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Traits\PathNamespace;

class ShopTicketServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'ShopTicket';

    protected string $nameLower = 'shopticket';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerConfig();
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/config.php', $this->nameLower,
        );
    }
}
