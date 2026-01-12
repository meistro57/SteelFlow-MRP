<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    protected $fillable = [
        'region',
        'tax_type',
        'rate',
        'effective_date',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'effective_date' => 'date',
    ];
}
