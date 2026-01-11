<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $employee_number
 * @property string|null $department
 * @property string|null $email
 * @property string|null $phone
 * @property \Illuminate\Support\Carbon|null $hire_date
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TimeEntry> $timeEntries
 */
class Employee extends Model
{
    protected $fillable = [
        'name',
        'employee_number',
        'department',
        'email',
        'phone',
        'hire_date',
        'is_active',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }
}
