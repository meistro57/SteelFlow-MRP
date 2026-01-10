<?php

namespace Modules\Backup\Console\Commands;

use Illuminate\Console\Command;
use Modules\Backup\Models\Backup;
use Modules\Backup\Services\RestoreService;

class BackupRestoreCommand extends Command
{
    protected $signature = 'backup:restore
                            {id : Backup ID to restore}
                            {--force : Skip confirmation}';

    protected $description = 'Restore from a backup';

    public function __construct(
        protected RestoreService $restoreService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $backupId = $this->argument('id');
        $force = $this->option('force');

        $backup = Backup::find($backupId);

        if (! $backup) {
            $this->error("Backup {$backupId} not found.");

            return self::FAILURE;
        }

        if (! $backup->isCompleted()) {
            $this->error("Backup is not in completed status (current status: {$backup->status}).");

            return self::FAILURE;
        }

        $this->warn('WARNING: This will restore the database from a backup.');
        $this->warn('A safety backup will be created before proceeding.');
        $this->table(
            ['ID', 'Name', 'Type', 'Created', 'Size'],
            [[
                $backup->id,
                $backup->name,
                $backup->type,
                $backup->created_at->format('Y-m-d H:i:s'),
                $backup->formatted_size,
            ]]
        );

        if (! $force) {
            if (! $this->confirm('Are you sure you want to restore from this backup?')) {
                $this->info('Restore cancelled.');

                return self::SUCCESS;
            }
        }

        $this->info('Creating safety backup...');

        try {
            $this->info('Starting restore operation...');

            $result = $this->restoreService->restore($backup);

            if ($result) {
                $this->info('Restore completed successfully!');
                $this->warn('Please verify your application is functioning correctly.');

                return self::SUCCESS;
            } else {
                $this->error('Restore failed!');

                return self::FAILURE;
            }
        } catch (\Throwable $exception) {
            $this->error("Restore failed: {$exception->getMessage()}");
            $this->warn('Check logs for safety backup information.');

            return self::FAILURE;
        }
    }
}
