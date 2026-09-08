<?php

namespace Modules\DrawingFlow\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FabQueueUpdateNotesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'shop_notes' => ['nullable', 'string', 'max:10000'],
            'notes' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
