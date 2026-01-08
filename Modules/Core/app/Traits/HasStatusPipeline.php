<?php
// Modules/Core/app/Traits/HasStatusPipeline.php

declare(strict_types=1);

namespace Modules\Core\Traits;

use Modules\Core\Status\StatusPipeline;
use Modules\Core\Status\StatusRegistry;

trait HasStatusPipeline
{
    abstract protected function statusPipelineName(): string;

    public function statusPipeline(): StatusPipeline
    {
        return StatusRegistry::pipeline($this->statusPipelineName());
    }

    public function statusLabel(?string $status = null): string
    {
        $current = $status ?? $this->status ?? '';

        if ($current === '') {
            return '';
        }

        return $this->statusPipeline()->label($current);
    }

    public function nextStatus(?string $status = null): ?string
    {
        $current = $status ?? $this->status ?? null;

        if ($current === null) {
            return null;
        }

        return $this->statusPipeline()->next($current);
    }

    public function hasValidStatus(?string $status = null): bool
    {
        $current = $status ?? $this->status ?? null;

        if ($current === null) {
            return false;
        }

        return $this->statusPipeline()->has($current);
    }
}
