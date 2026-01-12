<?php

namespace Modules\Authz\Providers;

use Illuminate\Support\ServiceProvider;

class AuthzServiceProvider extends ServiceProvider
{
    protected string $name = 'Authz';

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));
        $this->registerPolicies();
    }

    public function register(): void {}

    protected function registerPolicies(): void
    {
        // Register module-specific policies here
    }
}
