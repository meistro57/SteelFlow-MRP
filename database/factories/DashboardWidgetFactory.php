<?php

namespace Database\Factories;

use App\Models\Dashboard;
use App\Models\DashboardWidget;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DashboardWidget>
 */
class DashboardWidgetFactory extends Factory
{
    protected $model = DashboardWidget::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $widgetTypes = ['metrics-card', 'chart-bar', 'chart-line', 'table', 'activity-feed', 'quick-actions'];

        return [
            'dashboard_id' => Dashboard::factory(),
            'widget_type' => fake()->randomElement($widgetTypes),
            'title' => fake()->words(2, true),
            'grid_x' => fake()->numberBetween(0, 8),
            'grid_y' => fake()->numberBetween(0, 10),
            'grid_w' => fake()->numberBetween(2, 6),
            'grid_h' => fake()->numberBetween(2, 4),
            'min_w' => 2,
            'min_h' => 2,
            'config' => [],
            'data_source' => [],
            'is_visible' => true,
            'sort_order' => 0,
        ];
    }

    /**
     * Configure the widget as a metrics card.
     */
    public function metricsCard(): static
    {
        return $this->state(fn (array $attributes) => [
            'widget_type' => 'metrics-card',
            'grid_w' => 3,
            'grid_h' => 2,
            'config' => ['metric' => 'active_projects', 'icon' => 'folder'],
        ]);
    }

    /**
     * Configure the widget as a chart.
     */
    public function chart(string $type = 'bar'): static
    {
        return $this->state(fn (array $attributes) => [
            'widget_type' => "chart-{$type}",
            'grid_w' => 6,
            'grid_h' => 4,
            'config' => ['showLegend' => true],
        ]);
    }

    /**
     * Configure the widget as hidden.
     */
    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_visible' => false,
        ]);
    }
}
