<?php

namespace Modules\DrawingFlow\Http\Requests;

use Modules\DrawingFlow\Models\DrawingSubmittal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubmittalPurposeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'purpose' => ['required', Rule::in(array_keys(DrawingSubmittal::PURPOSE_OPTIONS))],
        ];
    }
}
