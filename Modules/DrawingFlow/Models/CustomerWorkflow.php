<?php

namespace Modules\DrawingFlow\Models;

use App\Models\Customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerWorkflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'requires_architect_approval',
        'requires_engineer_approval',
        'requires_gc_approval',
        'preferred_submittal_method',
        'submittal_email',
        'approval_sla_days',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'requires_architect_approval' => 'boolean',
            'requires_engineer_approval' => 'boolean',
            'requires_gc_approval' => 'boolean',
            'approval_sla_days' => 'integer',
        ];
    }

    // Relationships

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    // Helpers

    public function requiresMultipleApprovals(): bool
    {
        $count = 0;
        if ($this->requires_architect_approval) {
            $count++;
        }
        if ($this->requires_engineer_approval) {
            $count++;
        }
        if ($this->requires_gc_approval) {
            $count++;
        }

        return $count > 1;
    }
}
