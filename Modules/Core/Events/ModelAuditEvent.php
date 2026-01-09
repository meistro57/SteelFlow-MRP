<?php

// Modules/Core/app/Events/ModelAuditEvent.php

declare(strict_types=1);

namespace Modules\Core\Events;

use Illuminate\Database\Eloquent\Model;

class ModelAuditEvent
{
    /**
     * @param  array<string, mixed>  $changes
     * @param  array<string, mixed>  $original
     */
    public function __construct(
        public readonly Model $model,
        public readonly string $action,
        public readonly ?int $actorId,
        public readonly array $changes,
        public readonly array $original,
    ) {
    }
}
