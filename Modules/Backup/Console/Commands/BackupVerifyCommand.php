<?php

namespace Modules\Backup\Console\Commands;

use Illuminate\Console\Command;
use Modules\Backup\Models\Backup;
use Modules\Backup\Services\BackupService;

class BackupVerifyCommand extends Command
{
    protected $signature = 'backup:verify
                            {id? : Backup ID to verify (verifies all if not specified)}';

    protected $description = 'Verify backup integrity';

    public function __construct(
        protected BackupService $backupService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $backupId = $this->argument('id');

        if ($backupId) {
            return $this->verifySingle((int) $backupId);
        }

        return $this->verifyAll();
    }

    protected function verifySingle(int $backupId): int
    {
        $backup = Backup::find($backupId);

        if (! $backup) {
            $this->error("Backup {$backupId} not found.");

            return self::FAILURE;
        }

        $this->info("Verifying backup {$backup->name}...");

        try {
            $result = $this->backupService->verify($backup);

            if ($result) {
                $this->info('Backup verification passed!');

                return self::SUCCESS;
            }
            $this->error('Backup verification failed!');

            return self::FAILURE;
        } catch (\Throwable $exception) {
            $this->error("Verification failed: {$exception->getMessage()}");

            return self::FAILURE;
        }
    }

    protected function verifyAll(): int
    {
        $backups = Backup::where('status', 'completed')->get();

        if ($backups->isEmpty()) {
            $this->info('No backups to verify.');

            return self::SUCCESS;
        }

        $this->info("Verifying {$backups->count()} backup(s)...");

        $passed = 0;
        $failed = 0;

        foreach ($backups as $backup) {
            $result = $this->backupService->verify($backup);

            if ($result) {
                $this->line("✓ {$backup->name}");
                $passed++;
            } else {
                $this->error("✗ {$backup->name}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Verification complete: {$passed} passed, {$failed} failed.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
