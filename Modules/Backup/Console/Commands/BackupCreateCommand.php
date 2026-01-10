<?php

namespace Modules\Backup\Console\Commands;

use Illuminate\Console\Command;
use Modules\Backup\Services\BackupService;
use Modules\Backup\Services\StorageService;

class BackupCreateCommand extends Command
{
    protected $signature = 'backup:create
                            {--type=database : Type of backup (database, files, full)}
                            {--name= : Custom name for the backup}
                            {--cloud : Upload to cloud storage after creation}';

    protected $description = 'Create a new backup';

    public function __construct(
        protected BackupService $backupService,
        protected StorageService $storageService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $type = $this->option('type');
        $name = $this->option('name');
        $uploadToCloud = $this->option('cloud');

        $this->info("Creating {$type} backup...");

        try {
            // Ensure storage directory exists
            $this->storageService->ensureStorageDirectoryExists();

            // Create backup
            $backup = $this->backupService->create($type, [
                'name' => $name,
            ]);

            if ($backup->isCompleted()) {
                $this->info('Backup created successfully!');
                $this->table(
                    ['ID', 'Name', 'Type', 'Size', 'Path'],
                    [[
                        $backup->id,
                        $backup->name,
                        $backup->type,
                        $backup->formatted_size,
                        $backup->path,
                    ]],
                );

                // Upload to cloud if requested
                if ($uploadToCloud && config('backup.cloud.enabled')) {
                    $this->info('Uploading to cloud storage...');
                    $cloudPath = $this->storageService->uploadToCloud($backup->path);
                    $backup->update(['cloud_path' => $cloudPath]);
                    $this->info("Uploaded to cloud: {$cloudPath}");
                }

                return self::SUCCESS;
            }
            $this->error("Backup failed: {$backup->error_message}");

            return self::FAILURE;
        } catch (\Throwable $exception) {
            $this->error("Backup failed: {$exception->getMessage()}");

            return self::FAILURE;
        }
    }
}
