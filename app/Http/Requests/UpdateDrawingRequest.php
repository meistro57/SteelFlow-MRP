<?php

namespace App\Http\Requests;

use App\Models\Drawing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDrawingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Add project-based authorization here when available
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $drawingParam = $this->route('drawing');
        $drawingId = $drawingParam instanceof Drawing
            ? $drawingParam->id
            : (is_numeric($drawingParam) ? (int) $drawingParam : null);

        return [
            'project_id' => ['required', 'exists:projects,id'],
            'number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('drawings', 'number')
                    ->ignore($drawingId)
                    ->where(fn ($query) => $query->where('project_id', $this->project_id)),
            ],
            'revision' => ['nullable', 'string', 'max:10'],
            'title' => ['nullable', 'string', 'max:255'],
        ];
    }
}
