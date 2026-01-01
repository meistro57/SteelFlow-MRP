<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Phase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'code',
        'description',
        'sort_order',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function lots(): HasMany
    {
        return $this->hasMany(Lot::class);
    }
}
