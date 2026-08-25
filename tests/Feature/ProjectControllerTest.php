<?php

use App\Models\Lot;
use App\Models\Phase;
use App\Models\Project;
use App\Models\User;

it('shows a project with phases and lots for authenticated users', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create();

    $phase = Phase::query()->create([
        'project_id' => $project->id,
        'code' => 'P1',
        'description' => 'Phase 1',
        'sort_order' => 1,
    ]);

    Lot::query()->create([
        'project_id' => $project->id,
        'phase_id' => $phase->id,
        'code' => 'L1',
        'description' => 'Lot 1',
    ]);

    $response = $this->actingAs($user)->get(route('projects.show', $project));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Projects/Show')
        ->where('project.id', $project->id)
        ->has('project.phases', 1)
        ->has('project.lots', 1));
});

it('redirects guests when viewing a project', function () {
    $project = Project::factory()->create();

    $response = $this->get(route('projects.show', $project));

    $response->assertRedirect('/login');
});
