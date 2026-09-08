<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportSmlxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:zip,smlx', 'max:20480'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please select an SMLX file to import.',
            'file.mimes' => 'The file must be an SMLX archive (zip or smlx extension).',
            'file.max' => 'The file size must not exceed 20MB.',
        ];
    }
}
