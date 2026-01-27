<?php

use App\Models\Dashboard;
use App\Models\User;
use App\Services\UIEditorService;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    // Bypass policies during tests
    Gate::before(fn () => true);
});

describe('UIEditorController', function () {
    it('displays the dashboard builder index', function () {
        $response = $this->get(route('ui-editor.index'));
        $response->assertStatus(200);
    });

    it('creates a new dashboard', function () {
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        ]);
        $response = $this->post(route('ui-editor.store'), [
            'name' => 'Test Dashboard',
            'description' => 'A test dashboard',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('dashboards', [
            'name' => 'Test Dashboard',
            'user_id' => $this->user->id,
        ]);
    });

    it('displays a dashboard', function () {
        $dashboard = Dashboard::factory()->create(['user_id' => $this->user->id]);
        $response = $this->get(route('ui-editor.show', $dashboard));
        $response->assertStatus(200);
    });
});

describe('Widget Management', function () {
    it('adds a widget to a dashboard', function () {
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        ]);
        $dashboard = \App\Models\Dashboard::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->postJson(route('ui-editor.widgets.store', $dashboard), [
            'widget_type' => 'metrics-card',
            'title' => 'Test Widget',
            'grid_x' => 0,
            'grid_y' => 0,
            'grid_w' => 3,
            'grid_h' => 2,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('dashboard_widgets', [
            'dashboard_id' => $dashboard->id,
            'title' => 'Test Widget',
        ]);
    });
});

describe('UIEditorService', function () {
    it('creates a dashboard for a user', function () {
        $service = app(UIEditorService::class);
        $dashboard = $service->createDashboard($this->user, ['name' => 'Service Test']);
        expect($dashboard)->toBeInstanceOf(Dashboard::class);
        expect($dashboard->name)->toBe('Service Test');
    });
});
