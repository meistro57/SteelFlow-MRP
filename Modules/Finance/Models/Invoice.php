<?php

namespace Modules\Finance\Models;

use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $project_id
 * @property int $customer_id
 * @property string $invoice_number
 * @property \Illuminate\Support\Carbon $invoice_date
 * @property \Illuminate\Support\Carbon $due_date
 * @property float $subtotal_amount
 * @property float $tax_amount
 * @property float $total_amount
 * @property float $paid_amount
 * @property float $retention_amount
 * @property string $status
 * @property string|null $terms
 * @property string|null $notes
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Project $project
 * @property-read \App\Models\Customer $customer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Finance\Models\InvoiceLineItem> $lines
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Finance\Models\Payment> $payments
 * @property-read \App\Models\User $creator
 */
class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'customer_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'subtotal_amount',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'retention_amount',
        'status',
        'terms',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'retention_amount' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLineItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
