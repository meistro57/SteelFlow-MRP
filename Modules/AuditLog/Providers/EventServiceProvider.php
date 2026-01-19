<?php

namespace Modules\AuditLog\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\AuditLog\Listeners\LogModelAudit;
use Modules\Core\Events\ModelAuditEvent;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ModelAuditEvent::class => [
            LogModelAudit::class,
        ],
    ];
}
