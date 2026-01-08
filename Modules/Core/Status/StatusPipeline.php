<?php

// Modules/Core/app/Status/StatusPipeline.php

declare(strict_types=1);

namespace Modules\Core\Status;

use Illuminate\Support\Arr;

final class StatusPipeline
{
    /**
     * @var array<string, StatusDefinition>
     */
    private array $definitions;

    /**
     * @param  array<string, StatusDefinition>  $definitions
     */
    public function __construct(
        public readonly string $name,
        array $definitions,
    ) {
        $this->definitions = $definitions;
    }

    /**
     * @param  array<string, array<string, mixed>>  $definitions
     */
    public static function fromConfig(string $name, array $definitions): self
    {
        $mapped = [];

        foreach ($definitions as $key => $definition) {
            $mapped[$key] = StatusDefinition::fromArray($key, $definition);
        }

        return new self($name, $mapped);
    }

    /**
     * @return array<int, string>
     */
    public function keys(): array
    {
        return array_keys($this->definitions);
    }

    /**
     * @return array<string, string>
     */
    public function labels(): array
    {
        return Arr::map($this->definitions, static fn (StatusDefinition $definition) => $definition->label);
    }

    public function has(string $status): bool
    {
        return array_key_exists($status, $this->definitions);
    }

    public function definition(string $status): ?StatusDefinition
    {
        return $this->definitions[$status] ?? null;
    }

    public function next(string $status): ?string
    {
        return $this->definitions[$status]->next ?? null;
    }

    public function label(string $status): string
    {
        return $this->definitions[$status]->label ?? ucfirst(str_replace('_', ' ', $status));
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function toArray(): array
    {
        $output = [];

        foreach ($this->definitions as $key => $definition) {
            $output[$key] = $definition->toArray();
        }

        return $output;
    }
}
