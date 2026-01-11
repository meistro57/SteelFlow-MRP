<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $load_number
 * @property int $project_id
 * @property string $status
 * @property string|null $destination
 * @property \Illuminate\Support\Carbon|null $ship_date
 * @property string|null $carrier
 * @property string|null $truck_number
 * @property string|null $trailer_number
 * @property string|null $driver_name
 * @property float $total_weight_lbs
 * @property float $total_weight_kg
 * @property int $total_pieces
 * @property string|null $bol_number
 * @property \Illuminate\Support\Carbon|null $shipped_at
 * @property \Illuminate\Support\Carbon|null $delivered_at
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Project $project
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LoadItem> $items
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ShippingDocument> $documents
 */
class Load extends Model
{
    protected $fillable = [
        'load_number',
        'project_id',
        'status',
        'destination',
        'ship_date',
        'carrier',
        'truck_number',
        'trailer_number',
        'driver_name',
        'total_weight_lbs',
        'total_weight_kg',
        'total_pieces',
        'bol_number',
        'shipped_at',
        'delivered_at',
        'notes',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(LoadItem::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ShippingDocument::class);
    }
}
