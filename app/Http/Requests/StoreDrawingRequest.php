<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDrawingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Add project-based authorization here when available
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'exists:projects,id'],
            'number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('drawings', 'number')->where(fn ($query) => $query->where('project_id', $this->project_id)),
            ],
            'revision' => ['nullable', 'string', 'max:10'],
            'title' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'project_id.required' => 'Please select the project this drawing belongs to.',
            'project_id.exists' => 'The selected project could not be found.',
            'number.required' => 'A drawing number is required.',
        ];
    }
}
