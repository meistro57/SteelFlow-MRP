<?php

namespace Modules\DrawingFlow\Models;

use App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmittalApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'submittal_id',
        'approval_type',
        'approved_by',
        'approved_at',
        'reviewer_name',
        'reviewer_title',
        'reviewer_company',
        'reviewer_email',
        'approval_notes',
        'conditions',
        'approval_file_id',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    // Relationships

    public function submittal(): BelongsTo
    {
        return $this->belongsTo(DrawingSubmittal::class, 'submittal_id');
    }

    public function approvalFile(): BelongsTo
    {
        return $this->belongsTo(SubmittalFile::class, 'approval_file_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    // Helpers

    public function isApproved(): bool
    {
        return in_array($this->approval_type, ['approved', 'approved_as_noted']);
    }

    public function requiresRevision(): bool
    {
        return $this->approval_type === 'revise_and_resubmit';
    }

    public function isRejected(): bool
    {
        return $this->approval_type === 'rejected';
    }
}
