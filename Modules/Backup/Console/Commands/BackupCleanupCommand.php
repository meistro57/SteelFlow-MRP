<?php

namespace Modules\Backup\Console\Commands;

use Illuminate\Console\Command;
use Modules\Backup\Services\BackupService;

class BackupCleanupCommand extends Command
{
    protected $signature = 'backup:cleanup
                            {--days= : Number of days to retain backups (default from config)}
                            {--force : Skip confirmation}';

    protected $description = 'Clean up old backups based on retention policy';

    public function __construct(
        protected BackupService $backupService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $retentionDays = $this->option('days') ?? config('backup.database.retention_days', 30);
        $force = $this->option('force');

        $this->info("Cleaning up backups older than {$retentionDays} days...");

        if (! $force) {
            if (! $this->confirm('This will permanently delete old backups. Continue?')) {
                $this->info('Cleanup cancelled.');

                return self::SUCCESS;
            }
        }

        try {
            $deletedCount = $this->backupService->cleanupOldBackups($retentionDays);

            $this->info("Cleanup completed. Deleted {$deletedCount} backup(s).");

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->error("Cleanup failed: {$exception->getMessage()}");

            return self::FAILURE;
        }
    }
}
