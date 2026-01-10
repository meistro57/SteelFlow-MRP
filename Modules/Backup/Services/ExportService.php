<?php

namespace Modules\Backup\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Backup\Events\DataExportCompleted;
use Modules\Backup\Events\DataExportFailed;
use Modules\Backup\Models\DataExport;

class ExportService
{
    public function __construct(
        protected StorageService $storage,
    ) {}

    /**
     * Export data to a file.
     */
    public function export(string $type, string $format = 'csv', array $options = []): DataExport
    {
        $operationId = Str::uuid()->toString();

        Log::info('Starting data export', [
            'operation_id' => $operationId,
            'type' => $type,
            'options' => $options,
        ]);

        try {
            return DB::transaction(function () use ($type, $format, $options, $operationId) {
                // Create export record
                $export = DataExport::create([
                    'name' => $options['name'] ?? $this->generateExportName($type),
                    'type' => $type,
                    'format' => $format,
                    'status' => 'in_progress',
                    'filters' => $options['filters'] ?? null,
                    'started_at' => now(),
                    'created_by' => Auth::id(),
                ]);

                Log::info('Export record created', [
                    'operation_id' => $operationId,
                    'export_id' => $export->id,
                ]);

                try {
                    // Execute the export
                    $path = $this->executeExport($export, $options);

                    // Calculate file size and row count
                    $size = $this->storage->getFileSize($path);
                    $rowCount = $this->countRows($path, $export->format);

                    // Update export record
                    $export->update([
                        'status' => 'completed',
                        'path' => $path,
                        'size' => $size,
                        'row_count' => $rowCount,
                        'completed_at' => now(),
                    ]);

                    Log::info('Export completed successfully', [
                        'operation_id' => $operationId,
                        'export_id' => $export->id,
                        'path' => $path,
                        'row_count' => $rowCount,
                    ]);

                    // Dispatch export completed event
                    DataExportCompleted::dispatch($export);

                    return $export;
                } catch (\Throwable $exception) {
                    // Mark export as failed
                    $export->update([
                        'status' => 'failed',
                        'error_message' => $exception->getMessage(),
                        'completed_at' => now(),
                    ]);

                    // Dispatch export failed event
                    DataExportFailed::dispatch($export, $exception->getMessage());

                    throw $exception;
                }
            });
        } catch (\Throwable $exception) {
            Log::error('Export failed', [
                'operation_id' => $operationId,
                'type' => $type,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    /**
     * Execute the export based on type.
     */
    protected function executeExport(DataExport $export, array $options): string
    {
        $filename = "{$export->name}.{$export->format}";
        $path = config('backup.storage_path').DIRECTORY_SEPARATOR.'exports'.DIRECTORY_SEPARATOR.$filename;

        // Ensure exports directory exists
        $exportsDir = dirname($path);
        if (! is_dir($exportsDir)) {
            mkdir($exportsDir, 0755, true);
        }

        // Export based on type
        match ($export->type) {
            'projects' => $this->exportProjects($path, $export->format, $options['filters'] ?? []),
            'inventory' => $this->exportInventory($path, $export->format, $options['filters'] ?? []),
            'purchase_orders' => $this->exportPurchaseOrders($path, $export->format, $options['filters'] ?? []),
            default => throw new \InvalidArgumentException("Unsupported export type: {$export->type}"),
        };

        return $path;
    }

    /**
     * Export projects data.
     */
    protected function exportProjects(string $path, string $format, array $filters): void
    {
        $query = DB::table('projects')->select([
            'project_id',
            'name',
            'customer_id',
            'status',
            'start_date',
            'target_date',
            'created_at',
        ]);

        // Apply filters
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $this->writeToFile($query->get(), $path, $format);
    }

    /**
     * Export inventory data.
     */
    protected function exportInventory(string $path, string $format, array $filters): void
    {
        $query = DB::table('stock_items')->select([
            'stock_id',
            'material_id',
            'type',
            'size',
            'grade',
            'length',
            'status',
            'stock_area',
            'heat_number',
            'created_at',
        ]);

        // Apply filters
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $this->writeToFile($query->get(), $path, $format);
    }

    /**
     * Export purchase orders data.
     */
    protected function exportPurchaseOrders(string $path, string $format, array $filters): void
    {
        $query = DB::table('purchase_orders')->select([
            'po_number',
            'vendor_id',
            'project_id',
            'status',
            'order_date',
            'expected_date',
            'created_at',
        ]);

        // Apply filters
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $this->writeToFile($query->get(), $path, $format);
    }

    /**
     * Write data to file in specified format.
     */
    protected function writeToFile($data, string $path, string $format): void
    {
        match ($format) {
            'csv' => $this->writeCsv($data, $path),
            'json' => $this->writeJson($data, $path),
            default => throw new \InvalidArgumentException("Unsupported format: {$format}"),
        };
    }

    /**
     * Write data as CSV.
     */
    protected function writeCsv($data, string $path): void
    {
        $handle = fopen($path, 'wb');

        $firstRow = true;
        foreach ($data as $row) {
            $rowArray = (array) $row;

            // Write header
            if ($firstRow) {
                fputcsv($handle, array_keys($rowArray));
                $firstRow = false;
            }

            // Write data
            fputcsv($handle, $rowArray);
        }

        fclose($handle);
    }

    /**
     * Write data as JSON.
     */
    protected function writeJson($data, string $path): void
    {
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Count rows in exported file.
     */
    protected function countRows(string $path, string $format): int
    {
        return match ($format) {
            'csv' => max(0, count(file($path)) - 1), // Subtract header row
            'json' => count(json_decode(file_get_contents($path), true)),
            default => 0,
        };
    }

    /**
     * Generate export name.
     */
    protected function generateExportName(string $type): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');

        return "export_{$type}_{$timestamp}";
    }
}
