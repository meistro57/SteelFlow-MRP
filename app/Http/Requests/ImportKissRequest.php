<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportKissRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:txt,csv,kiss', 'max:20480'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please select a KISS file to import.',
            'file.mimes' => 'The file must be a KISS format file (txt, csv, or kiss extension).',
            'file.max' => 'The file size must not exceed 20MB.',
        ];
    }
}
