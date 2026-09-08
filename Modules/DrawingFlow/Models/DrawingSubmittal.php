<?php

namespace Modules\DrawingFlow\Models;

use App\Models\Customer;
use App\Models\Project;
use App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class DrawingSubmittal extends Model
{
    use HasFactory, SoftDeletes;

    public const PURPOSE_OPTIONS = [
        'for_fab' => 'FOR FAB',
        'for_approval' => 'For Approval',
        'for_material_order' => "For Mat'l. Order",
        'for_pricing' => 'For Pricing',
        'for_field_verification' => 'For field Verification',
        'preliminary' => 'Preliminary',
        'for_information' => 'For Information',
        'for_construction' => 'For Construction',
        'resubmittal' => 'Resubmittal',
    ];

    protected $fillable = [
        'submittal_number',
        'drawing_request_id',
        'project_id',
        'customer_id',
        'revision',
        'submitted_by_user_id',
        'submitted_at',
        'drawing_discipline',
        'purpose',
        'status',
        'approval_received_at',
        'approval_type',
        'approval_notes',
        'next_action',
        'notes',
        'internal_notes',
        'source_modified_at',
        'source_modified_by',
        'source_created_by',
        'source_status',
        'returned_status',
        'sent_to_customer',
        'model_link',
        'source_image',
        'mark_to_continue',
        'import_source',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'approval_received_at' => 'datetime',
            'source_modified_at' => 'datetime',
            'sent_to_customer' => 'boolean',
            'mark_to_continue' => 'boolean',
        ];
    }

    // Relationships

    public function drawingRequest(): BelongsTo
    {
        return $this->belongsTo(DrawingRequest::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(SubmittalFile::class, 'submittal_id');
    }

    public function currentFiles(): HasMany
    {
        return $this->hasMany(SubmittalFile::class, 'submittal_id')->where('is_current', true);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(SubmittalApproval::class, 'submittal_id');
    }

    public function submittalNotes(): HasMany
    {
        return $this->hasMany(SubmittalNote::class, 'submittal_id');
    }

    public function latestApproval(): HasOne
    {
        return $this->hasOne(SubmittalApproval::class, 'submittal_id')->latestOfMany();
    }

    public function fabQueueEntry(): HasOne
    {
        return $this->hasOne(FabQueue::class, 'submittal_id');
    }

    // Scopes

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // Helpers

    public function isApproved(): bool
    {
        return in_array($this->status, ['approved', 'approved_as_noted']);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function getPurposeLabelAttribute(): string
    {
        return self::PURPOSE_OPTIONS[$this->purpose] ?? ucfirst(str_replace('_', ' ', $this->purpose));
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'ready_to_submit' => 'Ready to Submit',
            'submitted' => 'Submitted',
            'approved' => 'Approved',
            'approved_as_noted' => 'Approved as Noted',
            'revise_and_resubmit' => 'Revise & Resubmit',
            'rejected' => 'Rejected',
            'field_verify_required' => 'Field Verify Required',
            'superseded' => 'Superseded',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get the next revision letter.
     */
    public static function nextRevision(string $current): string
    {
        return chr(ord($current) + 1);
    }
}
