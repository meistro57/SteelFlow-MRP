<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Project extends Model
{
    use SoftDeletes, Searchable;

    protected $fillable = [
        'job_number',
        'name',
        'customer_id',
        'status',
        'job_type',
        'po_number',
        'contract_weight_lbs',
        'contract_weight_kg',
        'ship_date',
        'notes',
    ];

    public function phases(): HasMany
    {
        return $this->hasMany(Phase::class);
    }

    public function lots(): HasMany
    {
        return $this->hasMany(Lot::class);
    }

    public function assemblies(): HasMany
    {
        return $this->hasMany(Assembly::class);
    }

    public function parts(): HasMany
    {
        return $this->hasMany(Part::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
