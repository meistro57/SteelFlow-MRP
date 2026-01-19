<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDashboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'columns' => ['nullable', 'integer', 'min:6', 'max:24'],
            'row_height' => ['nullable', 'integer', 'min:40', 'max:200'],
            'is_shared' => ['nullable', 'boolean'],
            'settings' => ['nullable', 'array'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Dashboard name is required.',
            'name.max' => 'Dashboard name cannot exceed 100 characters.',
        ];
    }
}
