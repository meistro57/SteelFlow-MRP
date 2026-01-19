<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $category
 * @property string|null $description
 * @property string|null $icon
 * @property string $component
 * @property int $default_w
 * @property int $default_h
 * @property int $min_w
 * @property int $min_h
 * @property int|null $max_w
 * @property int|null $max_h
 * @property array|null $default_config
 * @property array|null $config_schema
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|DashboardWidget[] $widgets
 */
class WidgetTemplate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'category',
        'description',
        'icon',
        'component',
        'default_w',
        'default_h',
        'min_w',
        'min_h',
        'max_w',
        'max_h',
        'default_config',
        'config_schema',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_config' => 'array',
            'config_schema' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function widgets(): HasMany
    {
        return $this->hasMany(DashboardWidget::class, 'widget_type', 'slug');
    }

    /**
     * Get templates grouped by category
     *
     * @return \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, WidgetTemplate>>
     */
    public static function getGroupedByCategory()
    {
        return collect(static::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get())
            ->groupBy('category');
    }

    /**
     * Create a widget instance from this template
     *
     * @return array{widget_type: string, title: string|null, grid_w: int, grid_h: int, min_w: int, min_h: int, config: array|null}
     */
    public function toWidgetDefaults(): array
    {
        return [
            'widget_type' => $this->slug,
            'title' => $this->name,
            'grid_w' => $this->default_w,
            'grid_h' => $this->default_h,
            'min_w' => $this->min_w,
            'min_h' => $this->min_h,
            'config' => $this->default_config,
        ];
    }
}
