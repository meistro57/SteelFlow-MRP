<?php

namespace Modules\Backup\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        \Modules\Backup\Events\BackupCompleted::class => [
            \Modules\Backup\Listeners\SendBackupCompletedNotification::class,
        ],
        \Modules\Backup\Events\BackupFailed::class => [
            \Modules\Backup\Listeners\SendBackupFailedNotification::class,
        ],
        \Modules\Backup\Events\DataExportCompleted::class => [
            \Modules\Backup\Listeners\SendDataExportCompletedNotification::class,
        ],
        \Modules\Backup\Events\DataExportFailed::class => [
            \Modules\Backup\Listeners\SendDataExportFailedNotification::class,
        ],
    ];

    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void {}
}
