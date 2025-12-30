<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Assembly extends Model
{
    use SoftDeletes, Searchable;
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

    public function parts(): HasMany
    {
        return $this->hasMany(Part::class);
    }

    public function instances(): HasMany
    {
        return $this->hasMany(AssemblyInstance::class);
    }
}
