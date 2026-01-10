<?php

namespace Modules\Backup\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class StorageService
{
    /**
     * Check if a file exists.
     */
    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * Get file size in bytes.
     */
    public function getFileSize(string $path): int
    {
        if (! file_exists($path)) {
            throw new \RuntimeException("File not found: {$path}");
        }

        return filesize($path);
    }

    /**
     * Delete a file.
     */
    public function delete(string $path): bool
    {
        if (! file_exists($path)) {
            Log::warning('Attempted to delete non-existent file', ['path' => $path]);

            return false;
        }

        return unlink($path);
    }

    /**
     * Upload backup to cloud storage.
     */
    public function uploadToCloud(string $localPath, string $cloudPath = null): string
    {
        if (! config('backup.cloud.enabled', false)) {
            throw new \RuntimeException('Cloud storage is not enabled');
        }

        $provider = config('backup.cloud.provider');
        $bucket = config('backup.cloud.bucket');

        if (empty($bucket)) {
            throw new \RuntimeException('Cloud storage bucket is not configured');
        }

        $cloudPath = $cloudPath ?? basename($localPath);
        $storagePath = config('backup.cloud.path', 'backups').'/'.$cloudPath;

        Log::info('Uploading backup to cloud storage', [
            'provider' => $provider,
            'bucket' => $bucket,
            'local_path' => $localPath,
            'cloud_path' => $storagePath,
        ]);

        try {
            // Upload to cloud storage
            $disk = Storage::disk($provider);
            $disk->put($storagePath, file_get_contents($localPath));

            Log::info('Backup uploaded to cloud storage', [
                'cloud_path' => $storagePath,
            ]);

            return $storagePath;
        } catch (\Throwable $exception) {
            Log::error('Cloud upload failed', [
                'provider' => $provider,
                'local_path' => $localPath,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    /**
     * Download backup from cloud storage.
     */
    public function downloadFromCloud(string $cloudPath, string $localPath): bool
    {
        if (! config('backup.cloud.enabled', false)) {
            throw new \RuntimeException('Cloud storage is not enabled');
        }

        $provider = config('backup.cloud.provider');

        Log::info('Downloading backup from cloud storage', [
            'provider' => $provider,
            'cloud_path' => $cloudPath,
            'local_path' => $localPath,
        ]);

        try {
            $disk = Storage::disk($provider);

            if (! $disk->exists($cloudPath)) {
                throw new \RuntimeException("Cloud backup not found: {$cloudPath}");
            }

            $content = $disk->get($cloudPath);
            file_put_contents($localPath, $content);

            Log::info('Backup downloaded from cloud storage', [
                'local_path' => $localPath,
            ]);

            return true;
        } catch (\Throwable $exception) {
            Log::error('Cloud download failed', [
                'provider' => $provider,
                'cloud_path' => $cloudPath,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    /**
     * Delete backup from cloud storage.
     */
    public function deleteFromCloud(string $cloudPath): bool
    {
        if (! config('backup.cloud.enabled', false)) {
            Log::info('Cloud storage is not enabled, skipping cloud delete');

            return true;
        }

        $provider = config('backup.cloud.provider');

        Log::info('Deleting backup from cloud storage', [
            'provider' => $provider,
            'cloud_path' => $cloudPath,
        ]);

        try {
            $disk = Storage::disk($provider);

            if ($disk->exists($cloudPath)) {
                $disk->delete($cloudPath);
            }

            Log::info('Backup deleted from cloud storage', [
                'cloud_path' => $cloudPath,
            ]);

            return true;
        } catch (\Throwable $exception) {
            Log::error('Cloud deletion failed', [
                'provider' => $provider,
                'cloud_path' => $cloudPath,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    /**
     * Ensure backup storage directory exists.
     */
    public function ensureStorageDirectoryExists(): void
    {
        $path = config('backup.storage_path');

        if (! is_dir($path)) {
            mkdir($path, 0755, true);
            Log::info('Created backup storage directory', ['path' => $path]);
        }
    }
}
