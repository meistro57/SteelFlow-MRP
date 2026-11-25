<?php

declare(strict_types=1);

namespace Modules\CncData\Console;

use Illuminate\Console\Command;
use Modules\CncData\Engine\ProgramAnalyzer;

/** php artisan cnc:analyze path/to/file.CNC [more files or folders] [--json] */
class AnalyzeCncCommand extends Command
{
    protected $signature = 'cnc:analyze {paths* : CNC files or folders} {--json : Print steelflow.cnc-analysis/1 JSON} {--rapid-rate= : Rapid rate in in/min}';

    protected $description = 'Read plasma CNC programs and report lengths, pierces, time and checks (stores nothing)';

    public function handle(ProgramAnalyzer $analyzer): int
    {
        $rapid = (float) ($this->option('rapid-rate') ?: config('cncdata.rapid_rate'));
        $files = ImportCncCommand::expand($this->argument('paths'));
        if (! $files) {
            $this->error('No .CNC/.NC files found.');

            return self::FAILURE;
        }

        $out = [];
        $rows = [];
        $errors = 0;
        foreach ($files as $f) {
            $a = $analyzer->analyze((string) file_get_contents($f), ['rapid_rate' => $rapid]);
            $out[basename($f)] = $a;
            $e = count(array_filter($a['warnings'], fn ($w) => $w['severity'] === 'error'));
            $errors += $e;
            $rows[] = [
                basename($f),
                trim(($a['process']['amps'] ? $a['process']['amps'].' A ' : '').($a['material']['thickness'] ? $a['material']['thickness'].'"' : '')),
                round($a['lengths']['cut'] / 12, 1).' ft',
                $a['pierces']['cut'],
                gmdate('i:s', (int) round($a['times_min']['total'] * 60)),
                $a['chart'] ? (($a['chart']['feed_ok'] && $a['chart']['kerf_ok']) ? 'match' : 'OFF') : '—',
                $e ? "<fg=red>{$e} error(s)</>" : (count($a['warnings']) ? count($a['warnings']).' warning(s)' : 'clean'),
            ];
        }

        if ($this->option('json')) {
            $this->line(json_encode(count($out) === 1 ? reset($out) : $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        } else {
            $this->table(['Program', 'Process', 'Cut', 'Pierces', 'Est. time', 'Cut chart', 'Checks'], $rows);
        }

        return $errors ? self::FAILURE : self::SUCCESS;
    }
}
