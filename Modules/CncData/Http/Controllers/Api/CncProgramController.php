<?php

declare(strict_types=1);

namespace Modules\CncData\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use InvalidArgumentException;
use Modules\CncData\Engine\ProgramAnalyzer;
use Modules\CncData\Http\Requests\RecordCncRunRequest;
use Modules\CncData\Http\Requests\StoreCncProgramRequest;
use Modules\CncData\Http\Resources\CncProgramResource;
use Modules\CncData\Models\CncProgram;
use Modules\CncData\Services\CncCostService;
use Modules\CncData\Services\CncProgramService;
use Modules\CncData\Services\ProductionRateService;

class CncProgramController extends Controller
{
    public function __construct(
        private readonly CncProgramService $programs,
        private readonly CncCostService $costs,
    ) {}

    /** Stateless: analyse a file and return steelflow.cnc-analysis/1 without storing anything. */
    public function analyze(Request $request, ProgramAnalyzer $analyzer): JsonResponse
    {
        $data = $request->validate([
            'file' => ['required_without:text', 'file', 'max:20480'],
            'text' => ['required_without:file', 'string'],
            'rapid_rate' => ['nullable', 'numeric', 'gt:0'],
        ]);
        $text = $request->hasFile('file') ? (string) $request->file('file')->get() : $data['text'];

        return response()->json($analyzer->analyze($text, ['rapid_rate' => $data['rapid_rate'] ?? config('cncdata.rapid_rate')]));
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $q = CncProgram::query()
            ->when($request->integer('project_id'), fn ($q, $v) => $q->where('project_id', $v))
            ->when($request->string('status')->toString(), fn ($q, $v) => $q->where('status', $v))
            ->when($request->boolean('flagged'), fn ($q) => $q->where('error_count', '>', 0))
            ->latest();

        return CncProgramResource::collection($q->paginate(min(100, $request->integer('per_page', 25))));
    }

    public function store(StoreCncProgramRequest $request): JsonResponse
    {
        [$contents, $name] = $request->program();
        try {
            $cncProgram = $this->programs->store($contents, $name, $request->safe()->only(['project_id', 'machine_id', 'rapid_rate', 'notes']), 'api');
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return (new CncProgramResource($cncProgram))->response()->setStatusCode($cncProgram->wasRecentlyCreated ? 201 : 200);
    }

    public function show(CncProgram $cncProgram): CncProgramResource
    {
        return new CncProgramResource($cncProgram->load(['parts', 'runs']));
    }

    public function reanalyze(Request $request, CncProgram $cncProgram): CncProgramResource
    {
        $rate = $request->validate(['rapid_rate' => ['nullable', 'numeric', 'gt:0']])['rapid_rate'] ?? null;

        return new CncProgramResource($this->programs->reanalyze($cncProgram, $rate ? (float) $rate : null));
    }

    public function approve(Request $request, CncProgram $cncProgram): JsonResponse
    {
        try {
            $this->programs->approve($cncProgram, $request->boolean('force'));
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return (new CncProgramResource($cncProgram))->response();
    }

    public function syncParts(Request $request, CncProgram $cncProgram): CncProgramResource
    {
        $data = $request->validate([
            'source' => ['nullable', 'in:manual,nest_report,filename'],
            'parts' => ['present', 'array'],
            'parts.*.piece_mark' => ['required', 'string', 'max:60'],
            'parts.*.quantity' => ['nullable', 'integer', 'min:1'],
            'parts.*.part_id' => ['nullable', 'integer', 'exists:parts,id'],
            'parts.*.assembly_id' => ['nullable', 'integer', 'exists:assemblies,id'],
        ]);

        return new CncProgramResource($this->programs->syncParts($cncProgram, $data['parts'], $data['source'] ?? 'manual'));
    }

    public function recordRun(RecordCncRunRequest $request, CncProgram $cncProgram): JsonResponse
    {
        $out = $this->programs->recordRun($cncProgram, $request->validated());

        return response()->json([
            'run' => $out['run'],
            'variance_alert' => $out['variance_alert'],
            'calibration' => $out['calibration'],
        ], 201);
    }

    public function cost(CncProgram $cncProgram): JsonResponse
    {
        return response()->json($this->costs->estimate($cncProgram));
    }

    public function rateBook(Request $request, ProductionRateService $rates): JsonResponse
    {
        return response()->json(['data' => $rates->rateBook($request->only(['machine_id', 'amps', 'material', 'thickness']))]);
    }
}
