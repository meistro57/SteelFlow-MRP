<?php

namespace Modules\AuditLog\Providers;

use Illuminate\Support\ServiceProvider;

class AuditLogServiceProvider extends ServiceProvider
{
    protected string $name = 'AuditLog';

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));
    }

    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
    }
}
