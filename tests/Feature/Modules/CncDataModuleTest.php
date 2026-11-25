<?php

use App\Models\Assembly;
use App\Models\Part;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\CncData\Enums\CncProgramStatus;
use Modules\CncData\Models\CncProcessRate;
use Modules\CncData\Models\CncProgram;
use Modules\CncData\Services\CncCostService;
use Modules\CncData\Services\CncProgramService;
use Modules\Inventory\Models\Grade;
use Modules\Inventory\Models\Material;

function cncTestMaterialId(): int
{
    $grade = Grade::query()->firstOrCreate(['code' => 'A36'], ['description' => 'ASTM A36', 'is_active' => true]);

    return Material::query()->firstOrCreate(
        ['type' => 'plate', 'size_imperial' => '1/2 x 60 x 220', 'grade_id' => $grade->id],
        ['unit_weight_lbs' => 20.4, 'unit_weight_kg' => 9.25, 'is_active' => true, 'sort_order' => 1],
    )->id;
}

function cncFixture(string $name): string
{
    return file_get_contents(base_path('tests/Fixtures/cnc/'.$name));
}

beforeEach(function () {
    Storage::fake('local');
    $this->user = User::factory()->create(['role' => 'admin']);
    $this->actingAs($this->user);
    $this->service = app(CncProgramService::class);
});

it('stores a program once and reads its production data', function () {
    $project = Project::factory()->create();
    $p = $this->service->store(cncFixture('3365_old_mill_rd-canopy01.CNC'), 'canopy01.CNC', ['project_id' => $project->id]);
    $again = $this->service->store(cncFixture('3365_old_mill_rd-canopy01.CNC'), 'canopy01.CNC', ['project_id' => $project->id]);

    expect($again->id)->toBe($p->id)
        ->and($p->status)->toBe(CncProgramStatus::Analyzed)
        ->and($p->amps)->toBe(170)
        ->and($p->thickness)->toEqual(0.75)
        ->and($p->cut_pierces)->toBe(19)
        ->and($p->chart_feed_ok)->toBeTrue()
        ->and($p->estimate_validated)->toBeFalse();
    Storage::disk('local')->assertExists($p->file_path);
});

it('flags programs with errors and blocks approval', function () {
    $p = $this->service->store(cncFixture('broken.CNC'), 'broken.CNC');

    expect($p->status)->toBe(CncProgramStatus::Flagged)->and($p->error_count)->toBeGreaterThan(0);
    expect(fn () => $this->service->approve($p))->toThrow(InvalidArgumentException::class);
    expect($this->service->approve($p, force: true)->status)->toBe(CncProgramStatus::Approved);
});

it('validates the estimate only after enough consistent runs', function () {
    $p = $this->service->store(cncFixture('3365_old_mill_rd-canopy04.CNC'), 'canopy04.CNC');
    $est = $p->est_minutes;

    foreach ([1.18, 1.22, 1.20, 1.19] as $k) {
        $this->service->recordRun($p, ['actual_minutes' => $est * $k]);
    }
    expect($p->fresh()->estimate_validated)->toBeFalse();

    $out = $this->service->recordRun($p, ['actual_minutes' => $est * 1.21]);
    $p->refresh();

    expect($out['calibration']['validated'])->toBeTrue()
        ->and($p->estimate_validated)->toBeTrue()
        ->and($p->calibration_factor)->toEqualWithDelta(1.20, 0.001)
        ->and($p->plannedMinutes())->toEqualWithDelta($est * 1.20, 0.001)
        ->and($p->status)->toBe(CncProgramStatus::Cut);
});

it('costs a program from shop rates and says when it is provisional', function () {
    $p = $this->service->store(cncFixture('rip-01.CNC'), 'rip-01.CNC');
    $cost = app(CncCostService::class);

    expect($cost->estimate($p)['available'])->toBeFalse();

    CncProcessRate::create(['amps' => 300, 'material' => 'Mild Steel', 'cost_per_pierce' => 0.50, 'cost_per_arc_minute' => 0.40, 'machine_rate_per_hour' => 120]);
    $c = $cost->estimate($p);

    expect($c['available'])->toBeTrue()
        ->and($c['validated'])->toBeFalse()
        ->and($c['pierce_cost'])->toEqual(0.50)
        ->and($c['total'])->toEqualWithDelta(0.50 + $p->arc_minutes * 0.40 + $p->est_minutes / 60 * 120, 0.02);
});

it('links piece marks to parts in the project', function () {
    $project = Project::factory()->create();
    $assemblyId = Assembly::create(['project_id' => $project->id, 'mark' => 'C1', 'quantity' => 1])->id;
    $partId = Part::create(['project_id' => $project->id, 'assembly_id' => $assemblyId, 'material_id' => cncTestMaterialId(), 'type' => 'PL', 'part_mark' => 'p1021', 'quantity' => 4])->id;
    $p = $this->service->store(cncFixture('rip-01.CNC'), 'rip-01.CNC', ['project_id' => $project->id]);
    $this->service->syncParts($p, [['piece_mark' => 'p1021', 'quantity' => 4], ['piece_mark' => 'p1013']]);

    expect($p->parts)->toHaveCount(2)
        ->and($p->parts->firstWhere('piece_mark', 'p1021')->part_id)->toBe($partId)
        ->and($p->parts->firstWhere('piece_mark', 'p1021')->quantity)->toBe(4)
        ->and($p->parts->firstWhere('piece_mark', 'p1013')->part_id)->toBeNull();

    // An assembly mark resolves to the assembly when no part carries it
    $this->service->syncParts($p, [['piece_mark' => 'C1']], 'nest_report');
    expect($p->fresh('parts')->parts->firstWhere('piece_mark', 'C1')->assembly_id)->toBe($assemblyId)
        ->and($p->fresh('parts')->parts)->toHaveCount(3);
});

it('serves the analysis contract over the API', function () {
    $this->post('/api/cnc/analyze', ['file' => UploadedFile::fake()->createWithContent('rip-01.CNC', cncFixture('rip-01.CNC'))])
        ->assertOk()
        ->assertJsonPath('schema', 'steelflow.cnc-analysis/1')
        ->assertJsonPath('process.amps', 300)
        ->assertJsonPath('chart.feed_ok', true);

    $id = $this->post('/api/cnc/programs', ['text' => cncFixture('rip-01.CNC'), 'program_name' => 'rip-01.CNC'])
        ->assertCreated()->json('data.id');

    $this->postJson("/api/cnc/programs/{$id}/runs", ['actual_minutes' => 3.1])->assertCreated();
    $this->getJson("/api/cnc/programs/{$id}")->assertOk()->assertJsonPath('data.status', 'cut');
    expect(CncProgram::find($id)->runs)->toHaveCount(1);
});
