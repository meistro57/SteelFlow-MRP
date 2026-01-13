<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportXsrRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:txt,csv,xsr', 'max:20480'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please select an XSR file to import.',
            'file.mimes' => 'The file must be an XSR format file (txt, csv, or xsr extension).',
            'file.max' => 'The file size must not exceed 20MB.',
        ];
    }
}
