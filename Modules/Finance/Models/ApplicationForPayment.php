<?php

namespace Modules\Finance\Models;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property float $retainage_rate
 */
class ApplicationForPayment extends Model
{
    use SoftDeletes;

    protected $table = 'applications_for_payment';

    protected $fillable = [
        'project_id',
        'application_number',
        'application_date',
        'total_value',
        'percent_complete',
        'retainage_rate',
        'retainage_amount',
        'previous_payments',
        'current_payment_due',
        'status',
        'created_by',
    ];

    protected $casts = [
        'application_date' => 'date',
        'total_value' => 'decimal:2',
        'percent_complete' => 'decimal:2',
        'retainage_rate' => 'decimal:2',
        'retainage_amount' => 'decimal:2',
        'previous_payments' => 'decimal:2',
        'current_payment_due' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ApplicationLineItem::class, 'application_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
