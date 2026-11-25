<?php

use App\Models\Assembly;
use App\Models\Part;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Modules\Inventory\Models\Grade;
use Modules\Inventory\Models\Material;

it('records who created assemblies and parts', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $project = Project::factory()->create();
    $assembly = Assembly::create(['project_id' => $project->id, 'mark' => 'B1', 'quantity' => 1]);
    $grade = Grade::query()->firstOrCreate(['code' => 'A36'], ['description' => 'ASTM A36', 'is_active' => true]);
    $material = Material::query()->firstOrCreate(
        ['type' => 'plate', 'size_imperial' => '3/8 x 48 x 96', 'grade_id' => $grade->id],
        ['unit_weight_lbs' => 15.3, 'unit_weight_kg' => 6.94, 'is_active' => true, 'sort_order' => 1],
    );
    $part = Part::create([
        'project_id' => $project->id, 'assembly_id' => $assembly->id, 'material_id' => $material->id,
        'type' => 'PL', 'part_mark' => 'p1', 'quantity' => 2,
    ]);

    expect($assembly->fresh()->created_by)->toBe($user->id)
        ->and($part->fresh()->created_by)->toBe($user->id)
        ->and($part->fresh()->updated_by)->toBe($user->id);
});

it('has audit columns on every table whose model records them', function (string $table, array $columns) {
    foreach ($columns as $column) {
        expect(Schema::hasColumn($table, $column))->toBeTrue("{$table}.{$column} is missing");
    }
})->with([
    'parts' => ['parts', ['created_by', 'updated_by']],
    'assemblies' => ['assemblies', ['created_by', 'updated_by']],
    'vendor_invoices' => ['vendor_invoices', ['created_by', 'updated_by']],
]);
