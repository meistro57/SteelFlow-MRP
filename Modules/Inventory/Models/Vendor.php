<?php

namespace Modules\Inventory\Models;

use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $address_1
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip
 * @property string|null $phone
 * @property string|null $fax
 * @property string|null $email
 * @property string|null $contact_name
 * @property string|null $payment_terms
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchaseOrder> $purchaseOrders
 */
class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'address_1',
        'city',
        'state',
        'zip',
        'phone',
        'fax',
        'email',
        'contact_name',
        'payment_terms',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
