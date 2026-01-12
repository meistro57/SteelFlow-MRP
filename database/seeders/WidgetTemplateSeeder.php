<?php

namespace Database\Seeders;

use App\Models\WidgetTemplate;
use Illuminate\Database\Seeder;

class WidgetTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            // Metrics Widgets
            [
                'name' => 'Metrics Card',
                'slug' => 'metrics-card',
                'category' => 'Metrics',
                'description' => 'Display a single KPI metric with trend indicator',
                'icon' => 'rectangle-group',
                'component' => 'MetricsCardWidget',
                'default_w' => 3,
                'default_h' => 2,
                'min_w' => 2,
                'min_h' => 2,
                'max_w' => 6,
                'max_h' => 4,
                'default_config' => ['metric' => 'active_projects', 'icon' => 'folder'],
                'config_schema' => [
                    'metric' => ['type' => 'select', 'options' => ['active_projects', 'total_weight', 'production_progress', 'ready_to_ship']],
                    'icon' => ['type' => 'select', 'options' => ['folder', 'scale', 'chart-bar', 'truck', 'bolt', 'cube']],
                ],
                'is_active' => true,
            ],

            // Chart Widgets
            [
                'name' => 'Bar Chart',
                'slug' => 'chart-bar',
                'category' => 'Charts',
                'description' => 'Horizontal or vertical bar chart for data comparison',
                'icon' => 'chart-bar',
                'component' => 'ChartWidget',
                'default_w' => 6,
                'default_h' => 4,
                'min_w' => 3,
                'min_h' => 3,
                'max_w' => 12,
                'max_h' => 8,
                'default_config' => ['chartTitle' => '', 'showLegend' => true],
                'config_schema' => [
                    'chartTitle' => ['type' => 'text'],
                    'showLegend' => ['type' => 'boolean'],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Line Chart',
                'slug' => 'chart-line',
                'category' => 'Charts',
                'description' => 'Line or area chart for trends over time',
                'icon' => 'chart-bar',
                'component' => 'ChartWidget',
                'default_w' => 6,
                'default_h' => 4,
                'min_w' => 3,
                'min_h' => 3,
                'max_w' => 12,
                'max_h' => 8,
                'default_config' => ['chartTitle' => '', 'showLegend' => true],
                'config_schema' => [
                    'chartTitle' => ['type' => 'text'],
                    'showLegend' => ['type' => 'boolean'],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Pie Chart',
                'slug' => 'chart-pie',
                'category' => 'Charts',
                'description' => 'Pie or doughnut chart for proportional data',
                'icon' => 'chart-pie',
                'component' => 'ChartWidget',
                'default_w' => 4,
                'default_h' => 4,
                'min_w' => 3,
                'min_h' => 3,
                'max_w' => 6,
                'max_h' => 6,
                'default_config' => ['chartTitle' => '', 'showLegend' => true],
                'config_schema' => [
                    'chartTitle' => ['type' => 'text'],
                    'showLegend' => ['type' => 'boolean'],
                ],
                'is_active' => true,
            ],

            // Data Widgets
            [
                'name' => 'Data Table',
                'slug' => 'table',
                'category' => 'Data',
                'description' => 'Sortable data table with pagination',
                'icon' => 'table-cells',
                'component' => 'TableWidget',
                'default_w' => 6,
                'default_h' => 4,
                'min_w' => 4,
                'min_h' => 3,
                'max_w' => 12,
                'max_h' => 10,
                'default_config' => ['pageSize' => 10],
                'config_schema' => [
                    'pageSize' => ['type' => 'number', 'min' => 5, 'max' => 50],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Activity Feed',
                'slug' => 'activity-feed',
                'category' => 'Data',
                'description' => 'Recent activity timeline with status indicators',
                'icon' => 'clock',
                'component' => 'ActivityFeedWidget',
                'default_w' => 4,
                'default_h' => 4,
                'min_w' => 3,
                'min_h' => 3,
                'max_w' => 6,
                'max_h' => 8,
                'default_config' => ['maxItems' => 5],
                'config_schema' => [
                    'maxItems' => ['type' => 'number', 'min' => 3, 'max' => 20],
                ],
                'is_active' => true,
            ],

            // Action Widgets
            [
                'name' => 'Quick Actions',
                'slug' => 'quick-actions',
                'category' => 'Actions',
                'description' => 'Grid of action buttons for common tasks',
                'icon' => 'bolt',
                'component' => 'QuickActionsWidget',
                'default_w' => 4,
                'default_h' => 3,
                'min_w' => 2,
                'min_h' => 2,
                'max_w' => 6,
                'max_h' => 6,
                'default_config' => [],
                'config_schema' => [],
                'is_active' => true,
            ],
            [
                'name' => 'Status Overview',
                'slug' => 'status-overview',
                'category' => 'Actions',
                'description' => 'Status summary with progress indicators',
                'icon' => 'document-text',
                'component' => 'StatusOverviewWidget',
                'default_w' => 4,
                'default_h' => 3,
                'min_w' => 3,
                'min_h' => 2,
                'max_w' => 6,
                'max_h' => 6,
                'default_config' => [],
                'config_schema' => [],
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            WidgetTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }
    }
}
