<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
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
            'theme' => ['sometimes', 'string', 'in:light,dark,system'],
            'layout_density' => ['sometimes', 'string', 'in:compact,comfortable,spacious'],
            'sidebar_collapsed' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'theme.in' => 'Theme must be one of: light, dark, system.',
            'layout_density.in' => 'Layout density must be one of: compact, comfortable, spacious.',
            'sidebar_collapsed.boolean' => 'Sidebar collapsed must be true or false.',
        ];
    }
}
