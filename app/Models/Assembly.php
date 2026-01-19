<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Modules\Core\Traits\HasAuditFields;

/**
 * @property int $id
 * @property int $project_id
 * @property string $mark
 * @property string|null $description
 * @property int $quantity
 * @property float|null $weight_each_lbs
 * @property float|null $weight_each_kg
 * @property float|null $total_weight_lbs
 * @property float|null $total_weight_kg
 * @property string|null $assembly_type
 * @property string|null $main_member_type
 * @property string|null $main_member_size
 * @property string|null $main_member_grade
 * @property float|null $main_member_length
 * @property int|null $drawing_id
 * @property string|null $model_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Project $project
 * @property-read \App\Models\Drawing|null $drawing
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Part[] $parts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AssemblyInstance[] $instances
 */
class Assembly extends Model
{
    use HasAuditFields, Searchable, SoftDeletes;

    protected $fillable = [
        'project_id',
        'mark',
        'description',
        'quantity',
        'weight_each_lbs',
        'weight_each_kg',
        'total_weight_lbs',
        'total_weight_kg',
        'assembly_type',
        'main_member_type',
        'main_member_size',
        'main_member_grade',
        'main_member_length',
        'drawing_id',
        'model_id',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function drawing(): BelongsTo
    {
        return $this->belongsTo(Drawing::class);
    }

    public function parts(): HasMany
    {
        return $this->hasMany(Part::class);
    }

    public function instances(): HasMany
    {
        return $this->hasMany(AssemblyInstance::class);
    }
}
