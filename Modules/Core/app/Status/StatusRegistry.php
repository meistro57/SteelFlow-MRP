<?php
// Modules/Core/app/Status/StatusRegistry.php

declare(strict_types=1);

namespace Modules\Core\Status;

use InvalidArgumentException;

final class StatusRegistry
{
    /**
     * @return array<string, array<string, array<string, mixed>>>
     */
    public static function all(): array
    {
        $pipelines = config('core.status_pipelines', []);

        return is_array($pipelines) ? $pipelines : [];
    }

    public static function has(string $name): bool
    {
        return array_key_exists($name, self::all());
    }

    public static function pipeline(string $name): StatusPipeline
    {
        $pipelines = self::all();

        if (! array_key_exists($name, $pipelines)) {
            throw new InvalidArgumentException("Status pipeline '{$name}' is not registered.");
        }

        $definition = $pipelines[$name];

        return StatusPipeline::fromConfig($name, $definition);
    }
}
