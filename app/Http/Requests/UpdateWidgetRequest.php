<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWidgetRequest extends FormRequest
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
            'title' => ['nullable', 'string', 'max:100'],
            'config' => ['nullable', 'array'],
            'data_source' => ['nullable', 'array'],
            'is_visible' => ['nullable', 'boolean'],
        ];
    }
}
