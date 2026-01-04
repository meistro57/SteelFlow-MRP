<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'job_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('projects')->ignore($this->route('project')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'status' => ['required', 'string', 'in:pending,active,on_hold,completed,cancelled'],
            'job_type' => ['nullable', 'string', 'in:new_construction,renovation,repair,miscellaneous'],
            'po_number' => ['nullable', 'string', 'max:100'],
            'contract_weight_lbs' => ['nullable', 'numeric', 'min:0'],
            'contract_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'ship_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'job_number.required' => 'A job number is required.',
            'job_number.unique' => 'This job number already exists.',
            'name.required' => 'A project name is required.',
            'status.in' => 'Invalid project status.',
            'customer_id.exists' => 'The selected customer does not exist.',
        ];
    }
}
