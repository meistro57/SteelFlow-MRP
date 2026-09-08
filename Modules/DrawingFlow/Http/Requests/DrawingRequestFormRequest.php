<?php

namespace Modules\DrawingFlow\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DrawingRequestFormRequest extends FormRequest
{
    public const DRAWING_TYPES = [
        'structural',
        'misc',
        'railings',
        'stairs',
        'handrail',
        'canopy',
        'other',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'exists:projects,id'],
            'customer_id' => ['required', 'exists:customers,id'],
            'assigned_to_user_id' => ['nullable', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'drawing_type' => ['required', Rule::in(self::DRAWING_TYPES)],
            'job_number' => ['nullable', 'string', 'max:100'],
            'customer_address' => ['nullable', 'string', 'max:255'],
            'required_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
