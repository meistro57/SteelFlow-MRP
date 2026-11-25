<?php

declare(strict_types=1);

namespace Modules\CncData\Services;

use App\Models\Assembly;
use App\Models\Part;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Modules\CncData\Engine\ProgramAnalyzer;
use Modules\CncData\Enums\CncProgramStatus;
use Modules\CncData\Models\CncProgram;
use Modules\CncData\Models\CncProgramPart;
use Modules\CncData\Models\CncRun;

class CncProgramService
{
    public function __construct(
        private readonly ProgramAnalyzer $analyzer,
        private readonly CalibrationService $calibration,
    ) {}

    /**
     * Store a program file and analyse it. Uploading the same file to the same
     * project again returns the existing record instead of a duplicate.
     *
     * @param  array{project_id?:?int,machine_id?:?int,rapid_rate?:?float,notes?:?string}  $attrs
     */
    public function store(string $contents, string $programName, array $attrs = [], string $source = 'upload'): CncProgram
    {
        if (trim($contents) === '') {
            throw new InvalidArgumentException('The CNC file is empty.');
        }
        $hash = hash('sha256', $contents);
        $projectId = $attrs['project_id'] ?? $this->suggestProject($programName)?->id;

        $existing = CncProgram::query()->where('file_hash', $hash)->where('project_id', $projectId)->first();
        if ($existing) {
            return $existing;
        }

        $disk = config('cncdata.disk');
        $path = trim(config('cncdata.path'), '/').'/'.substr($hash, 0, 2).'/'.$hash.'.cnc';
        if (! Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->put($path, $contents);
        }

        return DB::transaction(function () use ($contents, $programName, $attrs, $source, $hash, $projectId, $path) {
            $program = new CncProgram([
                'project_id' => $projectId,
                'machine_id' => $attrs['machine_id'] ?? null,
                'program_name' => $programName,
                'file_path' => $path,
                'file_hash' => $hash,
                'file_size' => strlen($contents),
                'source' => $source,
                'status' => CncProgramStatus::Pending,
                'rapid_rate' => $attrs['rapid_rate'] ?? config('cncdata.rapid_rate'),
                'notes' => $attrs['notes'] ?? null,
                'schema_version' => ProgramAnalyzer::SCHEMA,
                'analysis' => [],
            ]);

            return $this->applyAnalysis($program, $this->analyzer->analyze($contents, ['rapid_rate' => $program->rapid_rate]));
        });
    }

    /** Re-read the stored file, e.g. after the engine or cut-chart data improves. */
    public function reanalyze(CncProgram $program, ?float $rapidRate = null): CncProgram
    {
        $contents = Storage::disk(config('cncdata.disk'))->get($program->file_path);
        if ($contents === null) {
            throw new InvalidArgumentException("Stored file is missing: {$program->file_path}");
        }
        if ($rapidRate) {
            $program->rapid_rate = $rapidRate;
        }

        return DB::transaction(fn () => $this->applyAnalysis(
            $program,
            $this->analyzer->analyze($contents, ['rapid_rate' => $program->rapid_rate]),
        ));
    }

    /** @param array<string,mixed> $a  steelflow.cnc-analysis/1 */
    public function applyAnalysis(CncProgram $program, array $a): CncProgram
    {
        $errors = count(array_filter($a['warnings'], fn ($w) => $w['severity'] === 'error'));
        $warnings = count($a['warnings']) - $errors;
        $t = $a['times_min'];

        $status = $program->status ?? CncProgramStatus::Pending;
        if ($errors > 0) {
            $status = CncProgramStatus::Flagged;
        } elseif (in_array($status, [CncProgramStatus::Pending, CncProgramStatus::Flagged], true)) {
            $status = CncProgramStatus::Analyzed;
        }

        $program->fill([
            'status' => $status,
            'units' => $a['units'],
            'material_name' => $a['material']['name'],
            'thickness' => $a['material']['thickness'],
            'sheet_x' => $a['material']['sheet']['x'] ?? null,
            'sheet_y' => $a['material']['sheet']['y'] ?? null,
            'process_label' => $a['process']['label'],
            'amps' => $a['process']['amps'],
            'cut_length' => $a['lengths']['cut'],
            'mark_length' => $a['lengths']['mark'],
            'travel_length' => $a['lengths']['travel'],
            'rapid_length' => $a['lengths']['rapid'],
            'cut_pierces' => $a['pierces']['cut'],
            'true_hole_pierces' => $a['pierces']['true_hole'],
            'mark_starts' => $a['pierces']['mark_starts'],
            'est_minutes' => $t['total'],
            'arc_minutes' => round($t['cut'] + $t['mark'] + $t['pierce_delay'], 4),
            'error_count' => $errors,
            'warning_count' => $warnings,
            'chart_feed_ok' => $a['chart']['feed_ok'] ?? null,
            'chart_kerf_ok' => $a['chart']['kerf_ok'] ?? null,
            'schema_version' => $a['schema'],
            'analysis' => $a,
            'analyzed_at' => now(),
        ])->save();

        $this->calibration->refresh($program);

        return $program->refresh();
    }

    public function approve(CncProgram $program, bool $force = false): CncProgram
    {
        if ($program->error_count > 0 && ! $force) {
            throw new InvalidArgumentException("{$program->program_name} has {$program->error_count} error(s); fix the program or approve with force.");
        }
        $program->update(['status' => CncProgramStatus::Approved]);

        return $program;
    }

    /**
     * Replace the piece-mark links from one source. Marks are matched to the
     * program's project: parts by part_mark first, then assemblies by mark.
     *
     * @param  list<array{piece_mark:string,quantity?:int,part_id?:?int,assembly_id?:?int}>  $links
     */
    public function syncParts(CncProgram $program, array $links, string $source = 'manual'): CncProgram
    {
        DB::transaction(function () use ($program, $links, $source) {
            $program->parts()->where('source', $source)->delete();
            foreach ($links as $l) {
                $mark = trim($l['piece_mark']);
                $partId = $l['part_id'] ?? null;
                $assemblyId = $l['assembly_id'] ?? null;
                if (! $partId && ! $assemblyId && $program->project_id) {
                    $partId = Part::query()->where('project_id', $program->project_id)->where('part_mark', $mark)->value('id');
                    if (! $partId) {
                        $assemblyId = Assembly::query()->where('project_id', $program->project_id)->where('mark', $mark)->value('id');
                    }
                }
                CncProgramPart::create([
                    'cnc_program_id' => $program->id,
                    'part_id' => $partId,
                    'assembly_id' => $assemblyId,
                    'piece_mark' => $mark,
                    'quantity' => max(1, (int) ($l['quantity'] ?? 1)),
                    'source' => $source,
                ]);
            }
        });

        return $program->load('parts');
    }

    /**
     * Log an actual run. The estimate at that moment is snapshotted so later
     * re-analysis can't rewrite history, then calibration is refreshed.
     *
     * @param  array{actual_minutes:float,machine_id?:?int,operator_id?:?int,started_at?:mixed,finished_at?:mixed,arc_on_minutes?:?float,pierce_count?:?int,result?:string,source?:string,notes?:?string}  $data
     * @return array{run:CncRun,variance_alert:bool,calibration:array}
     */
    public function recordRun(CncProgram $program, array $data): array
    {
        return DB::transaction(function () use ($program, $data) {
            $est = $program->est_minutes;
            $actual = (float) $data['actual_minutes'];
            $variance = $est > 0 ? round(($actual - $est) / $est * 100, 2) : null;

            $run = $program->runs()->create([
                'machine_id' => $data['machine_id'] ?? $program->machine_id,
                'operator_id' => $data['operator_id'] ?? auth()->id(),
                'started_at' => $data['started_at'] ?? null,
                'finished_at' => $data['finished_at'] ?? now(),
                'actual_minutes' => $actual,
                'arc_on_minutes' => $data['arc_on_minutes'] ?? null,
                'pierce_count' => $data['pierce_count'] ?? null,
                'result' => $data['result'] ?? 'complete',
                'source' => $data['source'] ?? 'manual',
                'est_minutes_at_run' => $est,
                'variance_pct' => $variance,
                'notes' => $data['notes'] ?? null,
            ]);

            if ($run->result === 'complete' && $program->status !== CncProgramStatus::Archived) {
                $program->update(['status' => CncProgramStatus::Cut]);
            }
            $cal = $this->calibration->refreshGroup($run->machine_id, $program->amps, $program->thickness);

            return [
                'run' => $run,
                'variance_alert' => $variance !== null && abs($variance) > config('cncdata.variance_alert') * 100,
                'calibration' => $cal,
            ];
        });
    }

    /** Projects whose job number appears in the file name, e.g. "2065-base-plates-01.CNC". */
    public function suggestProject(string $programName): ?Project
    {
        $name = strtolower($programName);

        return Project::query()
            ->whereNotNull('job_number')
            ->where('job_number', '!=', '')
            ->where('status', '!=', 'archived')
            ->get(['id', 'job_number', 'name'])
            ->filter(fn (Project $p) => preg_match('/(^|[^a-z0-9])'.preg_quote(strtolower((string) $p->job_number), '/').'([^a-z0-9]|$)/', $name))
            ->sortByDesc(fn (Project $p) => strlen((string) $p->job_number))
            ->first();
    }
}
