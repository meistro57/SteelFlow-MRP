<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
