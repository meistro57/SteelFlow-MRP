<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessScanRequest extends FormRequest
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
            'barcode' => ['required', 'string', 'max:255'],
            'scan_type' => ['sometimes', 'string', 'in:part,assembly,load,stock'],
            'work_area_id' => ['sometimes', 'integer', 'exists:work_areas,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'barcode.required' => 'A barcode value is required.',
            'barcode.max' => 'The barcode cannot exceed 255 characters.',
            'scan_type.in' => 'Invalid scan type. Must be one of: part, assembly, load, stock.',
        ];
    }
}
