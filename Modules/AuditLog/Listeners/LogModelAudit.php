<?php

namespace Modules\AuditLog\Listeners;

use Illuminate\Support\Facades\Request;
use Modules\AuditLog\Models\AuditEntry;
use Modules\Core\Events\ModelAuditEvent;

class LogModelAudit
{
    /**
     * Handle the event.
     */
    public function handle(ModelAuditEvent $event): void
    {
        AuditEntry::create([
            'auditable_id' => $event->model->getKey(),
            'auditable_type' => $event->model->getMorphClass(),
            'user_id' => $event->actorId,
            'action' => $event->action,
            'changes' => $event->changes,
            'original' => $event->original,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
