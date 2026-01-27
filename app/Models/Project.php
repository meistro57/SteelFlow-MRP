<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;
use Modules\Core\Traits\HasAuditFields;
use Modules\Documents\Models\Document;

/**
 * @property int $id
 * @property string $job_number
 * @property string $name
 * @property int|null $customer_id
 * @property string $status
 * @property string|null $job_type
 * @property string|null $po_number
 * @property float|null $contract_weight_lbs
 * @property float|null $contract_weight_kg
 * @property \Illuminate\Support\Carbon|null $ship_date
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Phase[] $phases
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Lot[] $lots
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Assembly[] $assemblies
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Part[] $parts
 * @property-read \App\Models\Customer|null $customer
 * @property-read \Illuminate\Database\Eloquent\Collection|Document[] $documents
 */
class Project extends Model
{
    use HasAuditFields, HasFactory, Searchable, SoftDeletes;

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

    public function drawings(): HasMany
    {
        return $this->hasMany(Drawing::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get all of the project's documents.
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
