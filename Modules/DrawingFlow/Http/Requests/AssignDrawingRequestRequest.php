<?php

namespace Modules\DrawingFlow\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignDrawingRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
        ];
    }
}
