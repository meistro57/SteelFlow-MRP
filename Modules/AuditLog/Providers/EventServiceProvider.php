<?php

namespace Modules\AuditLog\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Core\Events\ModelAuditEvent;
use Modules\AuditLog\Listeners\LogModelAudit;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ModelAuditEvent::class => [
            LogModelAudit::class,
        ],
    ];
}
