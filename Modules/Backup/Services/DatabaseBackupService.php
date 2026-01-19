<?php

namespace Modules\Backup\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Modules\Backup\Models\Backup;

class DatabaseBackupService
{
    /**
     * Create a database backup.
     */
    public function backup(Backup $backupRecord): string
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if ($config['driver'] !== 'mysql') {
            throw new \RuntimeException('Only MySQL databases are currently supported');
        }

        $filename = "{$backupRecord->name}.sql";
        if (config('backup.database.compress', true)) {
            $filename .= '.gz';
        }

        $path = config('backup.storage_path').DIRECTORY_SEPARATOR.$filename;

        Log::info('Creating database backup', [
            'backup_id' => $backupRecord->id,
            'database' => $config['database'],
            'path' => $path,
        ]);

        // Build mysqldump command
        $command = $this->buildMysqldumpCommand($config, $path);

        // Execute the backup
        $result = Process::run($command);

        if (! $result->successful()) {
            throw new \RuntimeException("Database backup failed: {$result->errorOutput()}");
        }

        Log::info('Database backup completed', [
            'backup_id' => $backupRecord->id,
            'path' => $path,
        ]);

        return $path;
    }

    /**
     * Restore a database backup.
     */
    public function restore(string $path): bool
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if ($config['driver'] !== 'mysql') {
            throw new \RuntimeException('Only MySQL databases are currently supported');
        }

        Log::info('Restoring database backup', [
            'path' => $path,
            'database' => $config['database'],
        ]);

        if (! file_exists($path)) {
            throw new \RuntimeException("Backup file not found: {$path}");
        }

        // Build mysql restore command
        $command = $this->buildMysqlRestoreCommand($config, $path);

        // Execute the restore
        $result = Process::run($command);

        if (! $result->successful()) {
            throw new \RuntimeException("Database restore failed: {$result->errorOutput()}");
        }

        Log::info('Database restore completed', [
            'path' => $path,
        ]);

        return true;
    }

    /**
     * Verify database backup integrity.
     */
    public function verify(string $path): bool
    {
        if (! file_exists($path)) {
            return false;
        }

        // For gzipped files, verify they can be opened
        if (str_ends_with($path, '.gz')) {
            $gz = gzopen($path, 'rb');
            if ($gz === false) {
                return false;
            }
            // Read first few bytes to verify it's a valid SQL dump
            $header = gzread($gz, 100);
            gzclose($gz);

            return str_contains($header, 'MySQL dump') || str_contains($header, 'CREATE TABLE');
        }

        // For regular SQL files, verify they contain SQL content
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return false;
        }
        $header = fread($handle, 100);
        fclose($handle);

        return str_contains($header, 'MySQL dump') || str_contains($header, 'CREATE TABLE');
    }

    /**
     * Build mysqldump command.
     */
    protected function buildMysqldumpCommand(array $config, string $path): string
    {
        $host = $config['host'];
        $port = $config['port'];
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];

        $command = sprintf(
            'mysqldump --host=%s --port=%d --user=%s --password=%s %s',
            escapeshellarg($host),
            $port,
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
        );

        // Add compression if enabled
        if (config('backup.database.compress', true)) {
            $command .= ' | gzip > '.escapeshellarg($path);
        } else {
            $command .= ' > '.escapeshellarg($path);
        }

        return $command;
    }

    /**
     * Build mysql restore command.
     */
    protected function buildMysqlRestoreCommand(array $config, string $path): string
    {
        $host = $config['host'];
        $port = $config['port'];
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];

        // Handle compressed files
        if (str_ends_with($path, '.gz')) {
            $command = sprintf(
                'gunzip < %s | mysql --host=%s --port=%d --user=%s --password=%s %s',
                escapeshellarg($path),
                escapeshellarg($host),
                $port,
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
            );
        } else {
            $command = sprintf(
                'mysql --host=%s --port=%d --user=%s --password=%s %s < %s',
                escapeshellarg($host),
                $port,
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($path),
            );
        }

        return $command;
    }
}
