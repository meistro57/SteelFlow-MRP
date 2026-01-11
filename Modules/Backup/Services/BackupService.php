<?php

namespace Modules\Backup\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Backup\Events\BackupCompleted;
use Modules\Backup\Events\BackupFailed;
use Modules\Backup\Models\Backup;

class BackupService
{
    public function __construct(
        protected DatabaseBackupService $databaseBackup,
        protected StorageService $storage,
    ) {}

    /**
     * Create a new backup.
     */
    public function create(string $type = 'database', array $options = []): Backup
    {
        $operationId = Str::uuid()->toString();

        Log::info('Starting backup creation', [
            'operation_id' => $operationId,
            'type' => $type,
            'options' => $options,
        ]);

        // Create backup record outside of transaction to ensure it persists if matching throws
        $backup = Backup::create([
            'name' => $options['name'] ?? $this->generateBackupName($type),
            'type' => $type,
            'status' => 'in_progress',
            'started_at' => now(),
            'created_by' => Auth::id(),
            'notes' => $options['notes'] ?? null,
        ]);

        try {
            Log::info('Backup record created', [
                'operation_id' => $operationId,
                'backup_id' => $backup->id,
            ]);

            // Execute the backup based on type
            $path = match ($type) {
                'database' => $this->databaseBackup->backup($backup),
                default => throw new \InvalidArgumentException("Unsupported backup type: {$type}"),
            };

            // Calculate file size
            $size = $this->storage->getFileSize($path);

            // Update backup record
            $backup->update([
                'status' => 'completed',
                'path' => $path,
                'size' => $size,
                'completed_at' => now(),
            ]);

            Log::info('Backup completed successfully', [
                'operation_id' => $operationId,
                'backup_id' => $backup->id,
                'path' => $path,
                'size' => $size,
            ]);

            // Dispatch backup completed event
            BackupCompleted::dispatch($backup);

            return $backup;
        } catch (\Throwable $exception) {
            // Mark backup as failed
            $backup->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'completed_at' => now(),
            ]);

            // Dispatch backup failed event
            BackupFailed::dispatch($backup, $exception->getMessage());

            Log::error('Backup creation failed', [
                'operation_id' => $operationId,
                'type' => $type,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    /**
     * Delete a backup.
     */
    public function delete(Backup $backup): bool
    {
        Log::info('Deleting backup', [
            'backup_id' => $backup->id,
            'path' => $backup->path,
        ]);

        try {
            // Delete the backup file
            if ($backup->path) {
                $this->storage->delete($backup->path);
            }

            // Delete cloud backup if exists
            if ($backup->cloud_path) {
                $this->storage->deleteFromCloud($backup->cloud_path);
            }

            // Soft delete the record
            $backup->delete();

            Log::info('Backup deleted successfully', [
                'backup_id' => $backup->id,
            ]);

            return true;
        } catch (\Throwable $exception) {
            Log::error('Backup deletion failed', [
                'backup_id' => $backup->id,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    /**
     * Clean up old backups based on retention policy.
     */
    public function cleanupOldBackups(?int $retentionDays = null): int
    {
        $retentionDays = $retentionDays ?? config('backup.database.retention_days', 30);
        $cutoffDate = now()->subDays($retentionDays);

        Log::info('Starting backup cleanup', [
            'retention_days' => $retentionDays,
            'cutoff_date' => $cutoffDate,
        ]);

        $oldBackups = Backup::where('created_at', '<', $cutoffDate)
            ->where('status', 'completed')
            ->get();

        $deleted = 0;
        foreach ($oldBackups as $backup) {
            try {
                $this->delete($backup);
                $deleted++;
            } catch (\Throwable $exception) {
                Log::warning('Failed to delete old backup', [
                    'backup_id' => $backup->id,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        Log::info('Backup cleanup completed', [
            'deleted_count' => $deleted,
        ]);

        return $deleted;
    }

    /**
     * Generate a backup name.
     */
    protected function generateBackupName(string $type): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');

        return "backup_{$type}_{$timestamp}";
    }

    /**
     * Verify backup integrity.
     */
    public function verify(Backup $backup): bool
    {
        Log::info('Verifying backup integrity', [
            'backup_id' => $backup->id,
        ]);

        try {
            // Check if file exists
            if (! $this->storage->exists($backup->path)) {
                throw new \RuntimeException('Backup file not found');
            }

            // Verify file size matches
            $actualSize = $this->storage->getFileSize($backup->path);
            if ($actualSize !== $backup->size) {
                throw new \RuntimeException("File size mismatch: expected {$backup->size}, got {$actualSize}");
            }

            // Type-specific verification
            $result = match ($backup->type) {
                'database' => $this->databaseBackup->verify($backup->path),
                default => true,
            };

            Log::info('Backup verification completed', [
                'backup_id' => $backup->id,
                'result' => $result,
            ]);

            return $result;
        } catch (\Throwable $exception) {
            Log::error('Backup verification failed', [
                'backup_id' => $backup->id,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
