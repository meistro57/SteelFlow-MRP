<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLayoutRequest extends FormRequest
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
            'layout' => ['required', 'array'],
            'layout.*.i' => ['required', 'integer'],
            'layout.*.x' => ['required', 'integer', 'min:0'],
            'layout.*.y' => ['required', 'integer', 'min:0'],
            'layout.*.w' => ['required', 'integer', 'min:1'],
            'layout.*.h' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'layout.required' => 'Layout data is required.',
            'layout.*.i.required' => 'Widget ID is required.',
            'layout.*.x.required' => 'X position is required.',
            'layout.*.y.required' => 'Y position is required.',
            'layout.*.w.required' => 'Width is required.',
            'layout.*.h.required' => 'Height is required.',
        ];
    }
}
