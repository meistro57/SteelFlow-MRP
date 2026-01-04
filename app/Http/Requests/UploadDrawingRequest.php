<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadDrawingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Verify user has access to the drawing's project
        $drawing = $this->route('drawing');

        if (!$drawing) {
            return false;
        }

        // If you have project-based authorization, add it here
        // return $this->user()->canAccessProject($drawing->project_id);
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:pdf,dwg,dxf,jpg,jpeg,png',
                'max:10240', // 10MB max
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'A drawing file is required.',
            'file.file' => 'The upload must be a valid file.',
            'file.mimes' => 'The drawing must be a PDF, DWG, DXF, JPG, or PNG file.',
            'file.max' => 'The drawing file cannot exceed 10MB.',
        ];
    }
}
