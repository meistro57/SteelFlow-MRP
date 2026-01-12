<?php

use App\Models\Dashboard;
use App\Models\DashboardWidget;
use App\Models\User;
use App\Services\UIEditorService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('UIEditorController', function () {
    it('displays the dashboard builder index', function () {
        $response = $this->get(route('ui-editor.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('UIEditor/Index'));
    });

    it('displays the create dashboard form', function () {
        $response = $this->get(route('ui-editor.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('UIEditor/Create'));
    });

    it('creates a new dashboard', function () {
        $response = $this->post(route('ui-editor.store'), [
            'name' => 'Test Dashboard',
            'description' => 'A test dashboard',
            'columns' => 12,
            'row_height' => 80,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('dashboards', [
            'name' => 'Test Dashboard',
            'user_id' => $this->user->id,
        ]);
    });

    it('validates dashboard name is required', function () {
        $response = $this->post(route('ui-editor.store'), [
            'description' => 'A test dashboard',
        ]);

        $response->assertSessionHasErrors('name');
    });

    it('displays a dashboard', function () {
        $dashboard = Dashboard::factory()->create(['user_id' => $this->user->id]);

        $response = $this->get(route('ui-editor.show', $dashboard));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('UIEditor/Show')
            ->has('dashboard')
        );
    });

    it('displays the dashboard editor', function () {
        $dashboard = Dashboard::factory()->create(['user_id' => $this->user->id]);

        $response = $this->get(route('ui-editor.edit', $dashboard));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('UIEditor/Edit')
            ->has('dashboard')
            ->has('widgetTemplates')
        );
    });

    it('updates a dashboard', function () {
        $dashboard = Dashboard::factory()->create(['user_id' => $this->user->id]);

        $response = $this->put(route('ui-editor.update', $dashboard), [
            'name' => 'Updated Dashboard',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('dashboards', [
            'id' => $dashboard->id,
            'name' => 'Updated Dashboard',
        ]);
    });

    it('deletes a dashboard', function () {
        $dashboard = Dashboard::factory()->create(['user_id' => $this->user->id]);

        $response = $this->delete(route('ui-editor.destroy', $dashboard));

        $response->assertRedirect(route('ui-editor.index'));
        $this->assertSoftDeleted('dashboards', ['id' => $dashboard->id]);
    });

    it('prevents unauthorized users from editing others dashboards', function () {
        $otherUser = User::factory()->create();
        $dashboard = Dashboard::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->get(route('ui-editor.edit', $dashboard));

        $response->assertForbidden();
    });

    it('allows viewing shared dashboards', function () {
        $otherUser = User::factory()->create();
        $dashboard = Dashboard::factory()->create([
            'user_id' => $otherUser->id,
            'is_shared' => true,
        ]);

        $response = $this->get(route('ui-editor.show', $dashboard));

        $response->assertStatus(200);
    });
});

describe('Widget Management', function () {
    it('adds a widget to a dashboard', function () {
        $dashboard = Dashboard::factory()->create(['user_id' => $this->user->id]);

        $response = $this->postJson(route('ui-editor.widgets.store', $dashboard), [
            'widget_type' => 'metrics-card',
            'title' => 'Test Widget',
            'grid_x' => 0,
            'grid_y' => 0,
            'grid_w' => 3,
            'grid_h' => 2,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('dashboard_widgets', [
            'dashboard_id' => $dashboard->id,
            'widget_type' => 'metrics-card',
            'title' => 'Test Widget',
        ]);
    });

    it('updates a widget configuration', function () {
        $dashboard = Dashboard::factory()->create(['user_id' => $this->user->id]);
        $widget = DashboardWidget::factory()->create(['dashboard_id' => $dashboard->id]);

        $response = $this->putJson(route('ui-editor.widgets.update', [$dashboard, $widget]), [
            'title' => 'Updated Widget',
            'config' => ['metric' => 'total_weight'],
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('dashboard_widgets', [
            'id' => $widget->id,
            'title' => 'Updated Widget',
        ]);
    });

    it('removes a widget from a dashboard', function () {
        $dashboard = Dashboard::factory()->create(['user_id' => $this->user->id]);
        $widget = DashboardWidget::factory()->create(['dashboard_id' => $dashboard->id]);

        $response = $this->deleteJson(route('ui-editor.widgets.destroy', [$dashboard, $widget]));

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('dashboard_widgets', ['id' => $widget->id]);
    });

    it('updates the layout of all widgets', function () {
        $dashboard = Dashboard::factory()->create(['user_id' => $this->user->id]);
        $widget1 = DashboardWidget::factory()->create([
            'dashboard_id' => $dashboard->id,
            'grid_x' => 0,
            'grid_y' => 0,
        ]);
        $widget2 = DashboardWidget::factory()->create([
            'dashboard_id' => $dashboard->id,
            'grid_x' => 3,
            'grid_y' => 0,
        ]);

        $response = $this->putJson(route('ui-editor.layout.update', $dashboard), [
            'layout' => [
                ['i' => $widget1->id, 'x' => 0, 'y' => 2, 'w' => 4, 'h' => 3],
                ['i' => $widget2->id, 'x' => 4, 'y' => 2, 'w' => 4, 'h' => 3],
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $widget1->refresh();
        expect($widget1->grid_y)->toBe(2);
        expect($widget1->grid_w)->toBe(4);
    });
});

describe('UIEditorService', function () {
    it('creates a dashboard for a user', function () {
        $service = app(UIEditorService::class);

        $dashboard = $service->createDashboard($this->user, [
            'name' => 'Service Test Dashboard',
            'description' => 'Created via service',
        ]);

        expect($dashboard)->toBeInstanceOf(Dashboard::class);
        expect($dashboard->name)->toBe('Service Test Dashboard');
        expect($dashboard->user_id)->toBe($this->user->id);
    });

    it('duplicates a dashboard', function () {
        $service = app(UIEditorService::class);
        $original = Dashboard::factory()->create(['user_id' => $this->user->id]);
        DashboardWidget::factory()->count(3)->create(['dashboard_id' => $original->id]);

        $duplicate = $service->duplicateDashboard($original, $this->user, 'Duplicated Dashboard');

        expect($duplicate->name)->toBe('Duplicated Dashboard');
        expect($duplicate->widgets)->toHaveCount(3);
        expect($duplicate->id)->not->toBe($original->id);
    });

    it('sets a dashboard as default', function () {
        $service = app(UIEditorService::class);
        $dashboard1 = Dashboard::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => true,
        ]);
        $dashboard2 = Dashboard::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => false,
        ]);

        $service->setDefaultDashboard($this->user, $dashboard2);

        $dashboard1->refresh();
        $dashboard2->refresh();

        expect($dashboard1->is_default)->toBeFalse();
        expect($dashboard2->is_default)->toBeTrue();
    });

    it('creates a default dashboard for new users', function () {
        $service = app(UIEditorService::class);

        $dashboard = $service->createDefaultDashboard($this->user);

        expect($dashboard->is_default)->toBeTrue();
        expect($dashboard->widgets)->toHaveCount(6);
    });
});

describe('Dashboard Model', function () {
    it('generates a slug from the name', function () {
        $dashboard = Dashboard::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'My Test Dashboard',
        ]);

        expect($dashboard->slug)->toBe('my-test-dashboard');
    });

    it('returns the grid layout', function () {
        $dashboard = Dashboard::factory()->create(['user_id' => $this->user->id]);
        $widget = DashboardWidget::factory()->create([
            'dashboard_id' => $dashboard->id,
            'grid_x' => 0,
            'grid_y' => 0,
            'grid_w' => 4,
            'grid_h' => 3,
        ]);

        $layout = $dashboard->getGridLayout();

        expect($layout)->toHaveCount(1);
        expect($layout[0])->toMatchArray([
            'i' => $widget->id,
            'x' => 0,
            'y' => 0,
            'w' => 4,
            'h' => 3,
        ]);
    });
});
