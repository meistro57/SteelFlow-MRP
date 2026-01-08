<?php
// Modules/Core/app/Providers/CoreServiceProvider.php

declare(strict_types=1);

namespace Modules\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Traits\PathNamespace;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class CoreServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Core';

    protected string $nameLower = 'core';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerConfig();
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));
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
        $configPath = module_path($this->name, config('modules.paths.generator.config.path'));

        if (! is_dir($configPath)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($configPath));

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $config = str_replace($configPath.DIRECTORY_SEPARATOR, '', $file->getPathname());
                $configKey = str_replace([DIRECTORY_SEPARATOR, '.php'], ['.', ''], $config);
                $segments = explode('.', $this->nameLower.'.'.$configKey);

                $normalized = [];
                foreach ($segments as $segment) {
                    if (end($normalized) !== $segment) {
                        $normalized[] = $segment;
                    }
                }

                $key = ($config === 'config.php') ? $this->nameLower : implode('.', $normalized);

                $this->publishes([$file->getPathname() => config_path($config)], 'config');
                $this->mergeConfigFromPath($file->getPathname(), $key);
            }
        }
    }

    /**
     * Merge config from the given path recursively.
     */
    protected function mergeConfigFromPath(string $path, string $key): void
    {
        $existing = config($key, []);
        $moduleConfig = require $path;

        config([$key => array_replace_recursive($existing, $moduleConfig)]);
    }
}
