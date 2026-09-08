<?php

namespace Modules\DrawingFlow\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProcessSubmittalApprovalRequest extends FormRequest
{
    public const APPROVAL_TYPES = [
        'approved',
        'approved_as_noted',
        'revise_and_resubmit',
        'rejected',
        'field_verify_required',
    ];

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'approval_type' => ['required', Rule::in(self::APPROVAL_TYPES)],
            'reviewer_name' => ['nullable', 'string', 'max:255'],
            'reviewer_title' => ['nullable', 'string', 'max:255'],
            'reviewer_company' => ['nullable', 'string', 'max:255'],
            'reviewer_email' => ['nullable', 'email', 'max:255'],
            'approval_notes' => ['nullable', 'string'],
            'conditions' => ['nullable', 'string'],
        ];
    }
}
