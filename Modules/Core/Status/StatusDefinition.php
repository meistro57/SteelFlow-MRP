<?php

// Modules/Core/app/Status/StatusDefinition.php

declare(strict_types=1);

namespace Modules\Core\Status;

final class StatusDefinition
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly ?string $color = null,
        public readonly ?string $next = null,
        public readonly array $meta = [],
    ) {}

    /**
     * @param  array<string, mixed>  $definition
     */
    public static function fromArray(string $key, array $definition): self
    {
        $label = $definition['label'] ?? ucfirst(str_replace('_', ' ', $key));

        return new self(
            $key,
            $label,
            $definition['color'] ?? null,
            $definition['next'] ?? null,
            $definition['meta'] ?? [],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'color' => $this->color,
            'next' => $this->next,
            'meta' => $this->meta,
        ];
    }
}
