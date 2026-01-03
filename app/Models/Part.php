<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Part extends Model
{
    use Searchable, SoftDeletes;

    protected $fillable = [
        'project_id',
        'assembly_id',
        'part_mark',
        'material_id',
        'type',
        'size_imperial',
        'size_metric',
        'grade',
        'length',
        'quantity',
        'is_main_member',
        'weight_each_lbs',
        'weight_each_kg',
        'total_weight_lbs',
        'total_weight_kg',
        'finish',
        'remarks',
        'file_number',
        'unique_key',
        'nc_data_available',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assembly(): BelongsTo
    {
        return $this->belongsTo(Assembly::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function instances(): HasMany
    {
        return $this->hasMany(PartInstance::class);
    }
}
