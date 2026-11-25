<?php

declare(strict_types=1);

namespace Modules\CncData\Console;

use Illuminate\Console\Command;
use Modules\CncData\Services\CncProgramService;

/**
 * php artisan cnc:import X:\ --project=12 --machine=3
 *
 * Bulk-load programs from the ProNest output folder. Safe to run on a
 * schedule: files already stored for the same project are skipped by hash.
 */
class ImportCncCommand extends Command
{
    protected $signature = 'cnc:import {paths* : CNC files or folders} {--project= : Project id (otherwise matched from job number in the file name)} {--machine= : Machine id} {--rapid-rate=}';

    protected $description = 'Store and analyse plasma CNC programs';

    public function handle(CncProgramService $service): int
    {
        $files = self::expand($this->argument('paths'));
        $created = $existing = 0;
        foreach ($files as $f) {
            $p = $service->store((string) file_get_contents($f), basename($f), array_filter([
                'project_id' => $this->option('project') ? (int) $this->option('project') : null,
                'machine_id' => $this->option('machine') ? (int) $this->option('machine') : null,
                'rapid_rate' => $this->option('rapid-rate') ? (float) $this->option('rapid-rate') : null,
            ], fn ($v) => $v !== null), 'import');
            $p->wasRecentlyCreated ? $created++ : $existing++;
            $this->line(sprintf('%-40s %-9s project %s, %d error(s)', basename($f), $p->status->value, $p->project_id ?? '—', $p->error_count));
        }
        $this->info("{$created} stored, {$existing} already on file.");

        return self::SUCCESS;
    }

    /** @param list<string> $paths @return list<string> */
    public static function expand(array $paths): array
    {
        $files = [];
        foreach ($paths as $p) {
            if (is_dir($p)) {
                foreach (scandir($p) ?: [] as $f) {
                    if (preg_match('/\.(cnc|nc)$/i', $f)) {
                        $files[] = rtrim($p, '/\\').DIRECTORY_SEPARATOR.$f;
                    }
                }
            } elseif (is_file($p)) {
                $files[] = $p;
            }
        }

        return $files;
    }
}
