<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDashboardRequest;
use App\Http\Requests\StoreWidgetRequest;
use App\Http\Requests\UpdateDashboardRequest;
use App\Http\Requests\UpdateLayoutRequest;
use App\Http\Requests\UpdateWidgetRequest;
use App\Models\Dashboard;
use App\Models\DashboardWidget;
use App\Services\UIEditorService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UIEditorController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected UIEditorService $uiEditorService,
    ) {}

    /**
     * Display the dashboard builder index
     */
    public function index(): Response
    {
        $user = auth()->user();
        $dashboards = $this->uiEditorService->getUserDashboards($user);

        return Inertia::render('UIEditor/Index', [
            'dashboards' => $dashboards,
        ]);
    }

    /**
     * Show the form for creating a new dashboard
     */
    public function create(): Response
    {
        $templates = $this->uiEditorService->getWidgetTemplates();

        return Inertia::render('UIEditor/Create', [
            'widgetTemplates' => $templates,
        ]);
    }

    /**
     * Store a newly created dashboard
     */
    public function store(StoreDashboardRequest $request): RedirectResponse
    {
        $dashboard = $this->uiEditorService->createDashboard(
            $request->user(),
            $request->validated(),
        );

        return redirect()
            ->route('ui-editor.edit', $dashboard)
            ->with('success', 'Dashboard created successfully.');
    }

    /**
     * Display the specified dashboard
     */
    public function show(Dashboard $dashboard): Response
    {
        $this->authorize('view', $dashboard);

        $dashboard->load(['widgets' => fn ($q) => $q->where('is_visible', true)->orderBy('sort_order')]);

        $widgetsWithData = $dashboard->widgets->map(function (DashboardWidget $widget) {
            return [
                ...$widget->toArray(),
                'data' => $this->uiEditorService->getWidgetData($widget),
            ];
        });

        return Inertia::render('UIEditor/Show', [
            'dashboard' => $dashboard,
            'widgets' => $widgetsWithData,
            'layout' => $dashboard->getGridLayout(),
        ]);
    }

    /**
     * Show the dashboard editor (drag-and-drop builder)
     */
    public function edit(Dashboard $dashboard): Response
    {
        $this->authorize('update', $dashboard);

        $dashboard->load('widgets');
        $templates = $this->uiEditorService->getWidgetTemplates();

        $widgetsWithData = $dashboard->widgets->map(function (DashboardWidget $widget) {
            return [
                ...$widget->toArray(),
                'data' => $this->uiEditorService->getWidgetData($widget),
            ];
        });

        return Inertia::render('UIEditor/Edit', [
            'dashboard' => $dashboard,
            'widgets' => $widgetsWithData,
            'layout' => $dashboard->getGridLayout(),
            'widgetTemplates' => $templates,
        ]);
    }

    /**
     * Update dashboard settings
     */
    public function update(UpdateDashboardRequest $request, Dashboard $dashboard): RedirectResponse
    {
        $this->authorize('update', $dashboard);

        $this->uiEditorService->updateDashboard($dashboard, $request->validated());

        return redirect()
            ->route('ui-editor.edit', $dashboard)
            ->with('success', 'Dashboard updated successfully.');
    }

    /**
     * Delete a dashboard
     */
    public function destroy(Dashboard $dashboard): RedirectResponse
    {
        $this->authorize('delete', $dashboard);

        $this->uiEditorService->deleteDashboard($dashboard);

        return redirect()
            ->route('ui-editor.index')
            ->with('success', 'Dashboard deleted successfully.');
    }

    /**
     * Set a dashboard as default
     */
    public function setDefault(Dashboard $dashboard): RedirectResponse
    {
        $this->authorize('update', $dashboard);

        $this->uiEditorService->setDefaultDashboard(auth()->user(), $dashboard);

        return redirect()
            ->back()
            ->with('success', 'Default dashboard updated.');
    }

    /**
     * Duplicate a dashboard
     */
    public function duplicate(Request $request, Dashboard $dashboard): RedirectResponse
    {
        $this->authorize('view', $dashboard);

        $newName = $request->input('name', $dashboard->name.' (Copy)');
        $newDashboard = $this->uiEditorService->duplicateDashboard(
            $dashboard,
            $request->user(),
            $newName,
        );

        return redirect()
            ->route('ui-editor.edit', $newDashboard)
            ->with('success', 'Dashboard duplicated successfully.');
    }

    /**
     * Add a widget to the dashboard
     */
    public function addWidget(StoreWidgetRequest $request, Dashboard $dashboard): JsonResponse
    {
        $this->authorize('update', $dashboard);

        $widget = $this->uiEditorService->addWidget($dashboard, $request->validated());

        return response()->json([
            'success' => true,
            'widget' => [
                ...$widget->toArray(),
                'data' => $this->uiEditorService->getWidgetData($widget),
            ],
        ]);
    }

    /**
     * Update a widget's configuration
     */
    public function updateWidget(UpdateWidgetRequest $request, Dashboard $dashboard, DashboardWidget $widget): JsonResponse
    {
        $this->authorize('update', $dashboard);

        $updatedWidget = $this->uiEditorService->updateWidget($widget, $request->validated());

        return response()->json([
            'success' => true,
            'widget' => [
                ...$updatedWidget->toArray(),
                'data' => $this->uiEditorService->getWidgetData($updatedWidget),
            ],
        ]);
    }

    /**
     * Remove a widget from the dashboard
     */
    public function removeWidget(Dashboard $dashboard, DashboardWidget $widget): JsonResponse
    {
        $this->authorize('update', $dashboard);

        $this->uiEditorService->removeWidget($widget);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Update the layout (positions) of all widgets
     */
    public function updateLayout(UpdateLayoutRequest $request, Dashboard $dashboard): JsonResponse
    {
        $this->authorize('update', $dashboard);

        $this->uiEditorService->updateLayout($dashboard, $request->validated('layout'));

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Get widget data for refresh
     */
    public function getWidgetData(Dashboard $dashboard, DashboardWidget $widget): JsonResponse
    {
        $this->authorize('view', $dashboard);

        return response()->json([
            'data' => $this->uiEditorService->getWidgetData($widget),
        ]);
    }
}
