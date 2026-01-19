<?php

namespace Modules\UPF\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $next_filekey
 * @property int $next_orderkey
 * @property string|null $last_pkey
 * @property string|null $company_name
 * @property string $default_cost_method
 * @property array|null $settings
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
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
