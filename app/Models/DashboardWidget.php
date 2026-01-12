<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $dashboard_id
 * @property string $widget_type
 * @property string|null $title
 * @property int $grid_x
 * @property int $grid_y
 * @property int $grid_w
 * @property int $grid_h
 * @property int $min_w
 * @property int $min_h
 * @property array|null $config
 * @property array|null $data_source
 * @property bool $is_visible
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Dashboard $dashboard
 * @property-read WidgetTemplate|null $template
 */
class DashboardWidget extends Model
{
    protected $fillable = [
        'dashboard_id',
        'widget_type',
        'title',
        'grid_x',
        'grid_y',
        'grid_w',
        'grid_h',
        'min_w',
        'min_h',
        'config',
        'data_source',
        'is_visible',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'data_source' => 'array',
            'is_visible' => 'boolean',
        ];
    }

    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(Dashboard::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(WidgetTemplate::class, 'widget_type', 'slug');
    }

    /**
     * Get the grid item configuration
     *
     * @return array{i: int, x: int, y: int, w: int, h: int, minW: int, minH: int}
     */
    public function toGridItem(): array
    {
        return [
            'i' => $this->id,
            'x' => $this->grid_x,
            'y' => $this->grid_y,
            'w' => $this->grid_w,
            'h' => $this->grid_h,
            'minW' => $this->min_w,
            'minH' => $this->min_h,
        ];
    }

    /**
     * Update position from grid layout
     *
     * @param array{x: int, y: int, w: int, h: int} $position
     */
    public function updatePosition(array $position): void
    {
        $this->update([
            'grid_x' => $position['x'],
            'grid_y' => $position['y'],
            'grid_w' => $position['w'],
            'grid_h' => $position['h'],
        ]);
    }
}
