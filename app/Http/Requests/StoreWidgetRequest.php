<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWidgetRequest extends FormRequest
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
            'widget_type' => ['required', 'string', 'max:50'],
            'title' => ['nullable', 'string', 'max:100'],
            'grid_x' => ['nullable', 'integer', 'min:0'],
            'grid_y' => ['nullable', 'integer', 'min:0'],
            'grid_w' => ['nullable', 'integer', 'min:1', 'max:12'],
            'grid_h' => ['nullable', 'integer', 'min:1', 'max:12'],
            'config' => ['nullable', 'array'],
            'data_source' => ['nullable', 'array'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'widget_type.required' => 'Widget type is required.',
        ];
    }
}
