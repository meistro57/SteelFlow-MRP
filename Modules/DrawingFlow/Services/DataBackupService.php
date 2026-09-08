<?php

namespace Modules\DrawingFlow\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DataBackupService
{
    public function listBackups(): array
    {
        return collect(Storage::disk('local')->files('backups'))
            ->filter(fn (string $path) => str_ends_with($path, '.json'))
            ->map(function (string $path): array {
                return [
                    'path' => $path,
                    'name' => basename($path),
                    'size' => Storage::disk('local')->size($path),
                    'created_at' => Carbon::createFromTimestamp(Storage::disk('local')->lastModified($path))->toIso8601String(),
                ];
            })
            ->sortByDesc('created_at')
            ->values()
            ->all();
    }

    public function createBackup(): string
    {
        $timestamp = now()->format('Ymd_His');
        $filePath = "backups/drawingflow_backup_{$timestamp}.json";

        $payload = [
            'schema_version' => 1,
            'generated_at' => now()->toIso8601String(),
            'tables' => [],
        ];

        foreach ($this->backupTableNames() as $tableName) {
            $payload['tables'][$tableName] = DB::table($tableName)->get()->map(fn ($row): array => (array) $row)->all();
        }

        $payload['signature'] = $this->backupSignature($payload);

        Storage::disk('local')->put($filePath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $filePath;
    }

    public function restoreFromUpload(UploadedFile $backupFile): void
    {
        $this->restoreFromContents((string) $backupFile->get());
    }

    public function restoreFromContents(string $contents): void
    {
        $decoded = json_decode($contents, true);

        if (! is_array($decoded) || ! is_array(Arr::get($decoded, 'tables'))) {
            throw new RuntimeException('Invalid backup file format.');
        }

        if ((int) Arr::get($decoded, 'schema_version') !== 1) {
            throw new RuntimeException('Backup file schema version is not supported.');
        }

        $signature = Arr::get($decoded, 'signature');
        if (! is_string($signature) || $signature === '') {
            throw new RuntimeException('Backup file signature is missing.');
        }

        if (! hash_equals($this->backupSignature($decoded), $signature)) {
            throw new RuntimeException('Backup file signature is invalid.');
        }

        $tables = Arr::get($decoded, 'tables', []);
        $availableTables = $this->backupTableNames();

        foreach (array_keys($tables) as $tableName) {
            if (! in_array($tableName, $availableTables, true)) {
                throw new RuntimeException("Backup includes unsupported table [{$tableName}].");
            }
        }

        DB::transaction(function () use ($tables): void {
            Schema::disableForeignKeyConstraints();

            try {
                foreach (array_reverse(array_keys($tables)) as $tableName) {
                    DB::table($tableName)->truncate();
                }

                foreach ($tables as $tableName => $rows) {
                    if (! is_array($rows) || $rows === []) {
                        continue;
                    }

                    foreach (array_chunk($rows, 250) as $chunk) {
                        DB::table($tableName)->insert($chunk);
                    }
                }
            } finally {
                Schema::enableForeignKeyConstraints();
            }
        });
    }

    public function backupExists(string $fileName): bool
    {
        return Storage::disk('local')->exists($this->backupPathFromName($fileName));
    }

    public function backupPathFromName(string $fileName): string
    {
        if (basename($fileName) !== $fileName || ! str_ends_with($fileName, '.json')) {
            throw new RuntimeException('Invalid backup file name.');
        }

        return "backups/{$fileName}";
    }

    private function backupTableNames(): array
    {
        return collect(Schema::getTableListing())
            ->reject(fn (string $tableName) => in_array($tableName, ['migrations', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs'], true))
            ->values()
            ->all();
    }

    private function backupSignature(array $payload): string
    {
        $appKey = (string) config('app.key');

        if ($appKey === '') {
            throw new RuntimeException('Application key is required to sign backups.');
        }

        if (str_starts_with($appKey, 'base64:')) {
            $decodedKey = base64_decode(substr($appKey, 7), true);

            if ($decodedKey !== false) {
                $appKey = $decodedKey;
            }
        }

        $signaturePayload = [
            'schema_version' => Arr::get($payload, 'schema_version'),
            'generated_at' => Arr::get($payload, 'generated_at'),
            'tables' => Arr::get($payload, 'tables', []),
        ];

        $encodedPayload = json_encode($signaturePayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (! is_string($encodedPayload)) {
            throw new RuntimeException('Unable to encode backup payload for signing.');
        }

        return hash_hmac('sha256', $encodedPayload, $appKey);
    }
}
