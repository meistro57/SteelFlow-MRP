<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property bool $is_default
 * @property bool $is_shared
 * @property int $columns
 * @property int $row_height
 * @property array|null $settings
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read User $user
 * @property-read \Illuminate\Database\Eloquent\Collection|DashboardWidget[] $widgets
 */
class Dashboard extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'is_default',
        'is_shared',
        'columns',
        'row_height',
        'settings',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_shared' => 'boolean',
            'settings' => 'array',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Dashboard $dashboard) {
            if (empty($dashboard->slug)) {
                $dashboard->slug = Str::slug($dashboard->name);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function widgets(): HasMany
    {
        return $this->hasMany(DashboardWidget::class)->orderBy('sort_order');
    }

    /**
     * Get the layout configuration for the grid
     *
     * @return array<int, array{i: int, x: int, y: int, w: int, h: int}>
     */
    public function getGridLayout(): array
    {
        return $this->widgets->map(function (DashboardWidget $widget) {
            return [
                'i' => $widget->id,
                'x' => $widget->grid_x,
                'y' => $widget->grid_y,
                'w' => $widget->grid_w,
                'h' => $widget->grid_h,
                'minW' => $widget->min_w,
                'minH' => $widget->min_h,
            ];
        })->toArray();
    }

    /**
     * Duplicate this dashboard for a user
     */
    public function duplicate(User $user, string $newName): Dashboard
    {
        $newDashboard = $this->replicate();
        $newDashboard->user_id = $user->id;
        $newDashboard->name = $newName;
        $newDashboard->slug = Str::slug($newName);
        $newDashboard->is_default = false;
        $newDashboard->save();

        foreach ($this->widgets as $widget) {
            $newWidget = $widget->replicate();
            $newWidget->dashboard_id = $newDashboard->id;
            $newWidget->save();
        }

        return $newDashboard;
    }
}
