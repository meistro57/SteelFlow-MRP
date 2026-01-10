<?php

return [
    'name' => 'Backup',

    /*
    |--------------------------------------------------------------------------
    | Backup Storage Path
    |--------------------------------------------------------------------------
    |
    | This value determines where backup files will be stored on the local
    | filesystem. By default, backups are stored in storage/app/backups.
    |
    */
    'storage_path' => storage_path('app/backups'),

    /*
    |--------------------------------------------------------------------------
    | Database Backup Settings
    |--------------------------------------------------------------------------
    |
    | Configure database backup behavior including compression and retention.
    |
    */
    'database' => [
        'compress' => true,
        'retention_days' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cloud Storage Settings
    |--------------------------------------------------------------------------
    |
    | Configure optional cloud storage providers for backup sync.
    | Supported: 's3', 'azure'
    |
    */
    'cloud' => [
        'enabled' => env('BACKUP_CLOUD_ENABLED', false),
        'provider' => env('BACKUP_CLOUD_PROVIDER', 's3'),
        'bucket' => env('BACKUP_CLOUD_BUCKET', ''),
        'path' => env('BACKUP_CLOUD_PATH', 'backups'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Schedule Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for automatic backup schedules.
    |
    */
    'schedule' => [
        'enabled' => env('BACKUP_SCHEDULE_ENABLED', false),
        'frequency' => env('BACKUP_SCHEDULE_FREQUENCY', 'daily'),
        'time' => env('BACKUP_SCHEDULE_TIME', '02:00'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configure backup completion and failure notifications.
    |
    */
    'notifications' => [
        'enabled' => env('BACKUP_NOTIFICATIONS_ENABLED', true),
        'channels' => ['mail'],
        'recipients' => [
            env('BACKUP_NOTIFICATION_EMAIL', 'admin@steelflow.local'),
        ],
    ],
];
