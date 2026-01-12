<?php

namespace App\Services;

use App\Models\Dashboard;
use App\Models\DashboardWidget;
use App\Models\User;
use App\Models\WidgetTemplate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UIEditorService
{
    /**
     * Get all dashboards for a user
     *
     * @return Collection<int, Dashboard>
     */
    public function getUserDashboards(User $user): Collection
    {
        return Dashboard::where('user_id', $user->id)
            ->orWhere('is_shared', true)
            ->with('widgets')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get a dashboard with its widgets
     */
    public function getDashboard(int $dashboardId): ?Dashboard
    {
        return Dashboard::with(['widgets' => function ($query) {
            $query->where('is_visible', true)->orderBy('sort_order');
        }])->find($dashboardId);
    }

    /**
     * Create a new dashboard
     *
     * @param array{name: string, description?: string, columns?: int, row_height?: int, settings?: array} $data
     */
    public function createDashboard(User $user, array $data): Dashboard
    {
        return DB::transaction(function () use ($user, $data) {
            $dashboard = Dashboard::create([
                'user_id' => $user->id,
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'description' => $data['description'] ?? null,
                'columns' => $data['columns'] ?? 12,
                'row_height' => $data['row_height'] ?? 80,
                'settings' => $data['settings'] ?? [],
                'sort_order' => $this->getNextSortOrder($user),
            ]);

            return $dashboard;
        });
    }

    /**
     * Update dashboard settings
     *
     * @param array{name?: string, description?: string, columns?: int, row_height?: int, settings?: array, is_shared?: bool} $data
     */
    public function updateDashboard(Dashboard $dashboard, array $data): Dashboard
    {
        return DB::transaction(function () use ($dashboard, $data) {
            if (isset($data['name']) && $data['name'] !== $dashboard->name) {
                $data['slug'] = Str::slug($data['name']);
            }

            $dashboard->update($data);

            return $dashboard->fresh();
        });
    }

    /**
     * Delete a dashboard
     */
    public function deleteDashboard(Dashboard $dashboard): bool
    {
        return DB::transaction(function () use ($dashboard) {
            $dashboard->widgets()->delete();

            return $dashboard->delete();
        });
    }

    /**
     * Set a dashboard as the user's default
     */
    public function setDefaultDashboard(User $user, Dashboard $dashboard): void
    {
        DB::transaction(function () use ($user, $dashboard) {
            Dashboard::where('user_id', $user->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);

            $dashboard->update(['is_default' => true]);
        });
    }

    /**
     * Add a widget to a dashboard
     *
     * @param array{widget_type: string, title?: string, grid_x?: int, grid_y?: int, grid_w?: int, grid_h?: int, config?: array, data_source?: array} $data
     */
    public function addWidget(Dashboard $dashboard, array $data): DashboardWidget
    {
        $template = WidgetTemplate::where('slug', $data['widget_type'])->first();

        return DB::transaction(function () use ($dashboard, $data, $template) {
            $widgetData = [
                'dashboard_id' => $dashboard->id,
                'widget_type' => $data['widget_type'],
                'title' => $data['title'] ?? $template?->name ?? $data['widget_type'],
                'grid_x' => $data['grid_x'] ?? 0,
                'grid_y' => $data['grid_y'] ?? $this->getNextGridY($dashboard),
                'grid_w' => $data['grid_w'] ?? $template?->default_w ?? 4,
                'grid_h' => $data['grid_h'] ?? $template?->default_h ?? 3,
                'min_w' => $template?->min_w ?? 2,
                'min_h' => $template?->min_h ?? 2,
                'config' => $data['config'] ?? $template?->default_config ?? [],
                'data_source' => $data['data_source'] ?? [],
                'sort_order' => $this->getNextWidgetSortOrder($dashboard),
            ];

            return DashboardWidget::create($widgetData);
        });
    }

    /**
     * Update a widget's configuration
     *
     * @param array{title?: string, config?: array, data_source?: array, is_visible?: bool} $data
     */
    public function updateWidget(DashboardWidget $widget, array $data): DashboardWidget
    {
        $widget->update($data);

        return $widget->fresh();
    }

    /**
     * Update widget position on the grid
     *
     * @param array{x: int, y: int, w: int, h: int} $position
     */
    public function updateWidgetPosition(DashboardWidget $widget, array $position): DashboardWidget
    {
        $widget->updatePosition($position);

        return $widget->fresh();
    }

    /**
     * Bulk update widget positions (for drag-and-drop saves)
     *
     * @param array<int, array{i: int, x: int, y: int, w: int, h: int}> $layout
     */
    public function updateLayout(Dashboard $dashboard, array $layout): void
    {
        DB::transaction(function () use ($dashboard, $layout) {
            foreach ($layout as $item) {
                DashboardWidget::where('id', $item['i'])
                    ->where('dashboard_id', $dashboard->id)
                    ->update([
                        'grid_x' => $item['x'],
                        'grid_y' => $item['y'],
                        'grid_w' => $item['w'],
                        'grid_h' => $item['h'],
                    ]);
            }
        });
    }

    /**
     * Remove a widget from a dashboard
     */
    public function removeWidget(DashboardWidget $widget): bool
    {
        return $widget->delete();
    }

    /**
     * Duplicate a dashboard
     */
    public function duplicateDashboard(Dashboard $dashboard, User $user, string $newName): Dashboard
    {
        return $dashboard->duplicate($user, $newName);
    }

    /**
     * Get available widget templates grouped by category
     *
     * @return Collection<string, Collection<int, WidgetTemplate>>
     */
    public function getWidgetTemplates(): Collection
    {
        return WidgetTemplate::getGroupedByCategory();
    }

    /**
     * Get widget data for rendering
     *
     * @return array<string, mixed>
     */
    public function getWidgetData(DashboardWidget $widget): array
    {
        return match ($widget->widget_type) {
            'metrics-card' => $this->getMetricsCardData($widget),
            'chart-bar', 'chart-line', 'chart-pie', 'chart-doughnut' => $this->getChartData($widget),
            'table' => $this->getTableData($widget),
            'activity-feed' => $this->getActivityFeedData($widget),
            'quick-actions' => $this->getQuickActionsData($widget),
            'status-overview' => $this->getStatusOverviewData($widget),
            default => [],
        };
    }

    /**
     * Create default dashboard for new user
     */
    public function createDefaultDashboard(User $user): Dashboard
    {
        return DB::transaction(function () use ($user) {
            $dashboard = $this->createDashboard($user, [
                'name' => 'My Dashboard',
                'description' => 'Default dashboard',
            ]);

            $dashboard->update(['is_default' => true]);

            $this->addWidget($dashboard, [
                'widget_type' => 'metrics-card',
                'title' => 'Active Projects',
                'grid_x' => 0,
                'grid_y' => 0,
                'grid_w' => 3,
                'grid_h' => 2,
                'config' => ['metric' => 'active_projects', 'icon' => 'folder'],
            ]);

            $this->addWidget($dashboard, [
                'widget_type' => 'metrics-card',
                'title' => 'Total Weight',
                'grid_x' => 3,
                'grid_y' => 0,
                'grid_w' => 3,
                'grid_h' => 2,
                'config' => ['metric' => 'total_weight', 'icon' => 'scale'],
            ]);

            $this->addWidget($dashboard, [
                'widget_type' => 'metrics-card',
                'title' => 'Production Progress',
                'grid_x' => 6,
                'grid_y' => 0,
                'grid_w' => 3,
                'grid_h' => 2,
                'config' => ['metric' => 'production_progress', 'icon' => 'chart-bar'],
            ]);

            $this->addWidget($dashboard, [
                'widget_type' => 'metrics-card',
                'title' => 'Ready to Ship',
                'grid_x' => 9,
                'grid_y' => 0,
                'grid_w' => 3,
                'grid_h' => 2,
                'config' => ['metric' => 'ready_to_ship', 'icon' => 'truck'],
            ]);

            $this->addWidget($dashboard, [
                'widget_type' => 'activity-feed',
                'title' => 'Recent Activity',
                'grid_x' => 0,
                'grid_y' => 2,
                'grid_w' => 6,
                'grid_h' => 4,
            ]);

            $this->addWidget($dashboard, [
                'widget_type' => 'quick-actions',
                'title' => 'Quick Actions',
                'grid_x' => 6,
                'grid_y' => 2,
                'grid_w' => 6,
                'grid_h' => 4,
            ]);

            return $dashboard->fresh(['widgets']);
        });
    }

    /**
     * Get metrics card data
     *
     * @return array{value: mixed, change?: float, trend?: string}
     */
    protected function getMetricsCardData(DashboardWidget $widget): array
    {
        $reportingService = app(ReportingService::class);
        $metrics = $reportingService->getDashboardMetrics();
        $metric = $widget->config['metric'] ?? 'active_projects';

        return [
            'value' => $metrics[$metric] ?? 0,
            'trend' => 'up',
            'change' => 5.2,
        ];
    }

    /**
     * Get chart data
     *
     * @return array{labels: array, datasets: array}
     */
    protected function getChartData(DashboardWidget $widget): array
    {
        return [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'datasets' => [
                [
                    'label' => $widget->title,
                    'data' => [12, 19, 3, 5, 2, 3],
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                ],
            ],
        ];
    }

    /**
     * Get table data
     *
     * @return array{columns: array, rows: array}
     */
    protected function getTableData(DashboardWidget $widget): array
    {
        return [
            'columns' => ['ID', 'Name', 'Status'],
            'rows' => [],
        ];
    }

    /**
     * Get activity feed data
     *
     * @return array{items: array}
     */
    protected function getActivityFeedData(DashboardWidget $widget): array
    {
        $reportingService = app(ReportingService::class);
        $stats = $reportingService->getProductionStats();

        return [
            'items' => $stats['recentActivity'] ?? [],
        ];
    }

    /**
     * Get quick actions data
     *
     * @return array{actions: array}
     */
    protected function getQuickActionsData(DashboardWidget $widget): array
    {
        return [
            'actions' => [
                ['label' => 'New Project', 'route' => 'projects.create', 'icon' => 'plus'],
                ['label' => 'Import BOM', 'route' => 'bom.import', 'icon' => 'upload'],
                ['label' => 'View Reports', 'route' => 'reports.index', 'icon' => 'document-report'],
            ],
        ];
    }

    /**
     * Get status overview data
     *
     * @return array{statuses: array}
     */
    protected function getStatusOverviewData(DashboardWidget $widget): array
    {
        return [
            'statuses' => [
                ['label' => 'In Progress', 'count' => 12, 'color' => 'blue'],
                ['label' => 'Complete', 'count' => 45, 'color' => 'green'],
                ['label' => 'Pending', 'count' => 8, 'color' => 'yellow'],
            ],
        ];
    }

    protected function getNextSortOrder(User $user): int
    {
        return (Dashboard::where('user_id', $user->id)->max('sort_order') ?? 0) + 1;
    }

    protected function getNextWidgetSortOrder(Dashboard $dashboard): int
    {
        return ($dashboard->widgets()->max('sort_order') ?? 0) + 1;
    }

    protected function getNextGridY(Dashboard $dashboard): int
    {
        $maxY = $dashboard->widgets()
            ->selectRaw('MAX(grid_y + grid_h) as max_y')
            ->value('max_y');

        return $maxY ?? 0;
    }
}
