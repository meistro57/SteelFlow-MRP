<?php

namespace Modules\Backup\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Backup\Models\Backup;

class RestoreService
{
    public function __construct(
        protected DatabaseBackupService $databaseBackup,
        protected StorageService $storage,
    ) {}

    /**
     * Restore from a backup.
     */
    public function restore(Backup $backup): bool
    {
        $operationId = Str::uuid()->toString();

        Log::info('Starting restore operation', [
            'operation_id' => $operationId,
            'backup_id' => $backup->id,
            'type' => $backup->type,
        ]);

        try {
            // Verify backup exists
            if (! $this->storage->exists($backup->path)) {
                throw new \RuntimeException('Backup file not found');
            }

            // Create a safety backup before restore
            $safetyBackup = $this->createSafetyBackup($operationId);

            try {
                // Perform the restore based on type
                $result = match ($backup->type) {
                    'database' => $this->databaseBackup->restore($backup->path),
                    default => throw new \InvalidArgumentException("Unsupported backup type: {$backup->type}"),
                };

                Log::info('Restore completed successfully', [
                    'operation_id' => $operationId,
                    'backup_id' => $backup->id,
                    'safety_backup_id' => $safetyBackup->id,
                ]);

                return $result;
            } catch (\Throwable $exception) {
                Log::error('Restore failed, safety backup available', [
                    'operation_id' => $operationId,
                    'backup_id' => $backup->id,
                    'safety_backup_id' => $safetyBackup->id,
                    'message' => $exception->getMessage(),
                ]);

                throw $exception;
            }
        } catch (\Throwable $exception) {
            Log::error('Restore operation failed', [
                'operation_id' => $operationId,
                'backup_id' => $backup->id,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    /**
     * Create a safety backup before restore.
     */
    protected function createSafetyBackup(string $operationId): Backup
    {
        Log::info('Creating safety backup', [
            'operation_id' => $operationId,
        ]);

        return DB::transaction(function () use ($operationId) {
            $backup = Backup::create([
                'name' => 'safety_backup_'.now()->format('Y-m-d_H-i-s'),
                'type' => 'database',
                'status' => 'in_progress',
                'started_at' => now(),
                'created_by' => Auth::id(),
                'notes' => "Safety backup created before restore (operation: {$operationId})",
            ]);

            try {
                $path = $this->databaseBackup->backup($backup);
                $size = $this->storage->getFileSize($path);

                $backup->update([
                    'status' => 'completed',
                    'path' => $path,
                    'size' => $size,
                    'completed_at' => now(),
                ]);

                Log::info('Safety backup created successfully', [
                    'operation_id' => $operationId,
                    'backup_id' => $backup->id,
                ]);

                return $backup;
            } catch (\Throwable $exception) {
                $backup->update([
                    'status' => 'failed',
                    'error_message' => $exception->getMessage(),
                    'completed_at' => now(),
                ]);

                throw $exception;
            }
        });
    }

    /**
     * Download and restore from cloud backup.
     */
    public function restoreFromCloud(Backup $backup): bool
    {
        if (! $backup->cloud_path) {
            throw new \RuntimeException('Backup does not have a cloud path');
        }

        Log::info('Restoring from cloud backup', [
            'backup_id' => $backup->id,
            'cloud_path' => $backup->cloud_path,
        ]);

        // Download from cloud if local file doesn't exist
        if (! $this->storage->exists($backup->path)) {
            $this->storage->downloadFromCloud($backup->cloud_path, $backup->path);
        }

        // Restore from local file
        return $this->restore($backup);
    }
}
