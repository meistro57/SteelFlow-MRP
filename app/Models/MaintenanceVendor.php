<?php

// MaintenanceVendor.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $service_type
 * @property string|null $contact_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $address
 * @property string|null $website
 * @property string|null $emergency_contact
 * @property string|null $notes
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MaintenancePart> $parts
 */
class MaintenanceVendor extends Model
{
    protected $fillable = [
        'name',
        'service_type',
        'contact_name',
        'email',
        'phone',
        'address',
        'website',
        'emergency_contact',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parts(): HasMany
    {
        return $this->hasMany(MaintenancePart::class, 'vendor_id');
    }
}
