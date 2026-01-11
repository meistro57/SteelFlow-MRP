<?php

namespace Modules\UPF\Models;

use Illuminate\Database\Eloquent\Model;

class SystemConfig extends Model
{
    protected $table = 'system_config';

    protected $fillable = [
        'next_filekey',
        'next_orderkey',
        'last_pkey',
        'company_name',
        'default_cost_method',
        'settings',
    ];

    protected $casts = [
        'next_filekey' => 'integer',
        'next_orderkey' => 'integer',
        'settings' => 'json',
    ];
}
