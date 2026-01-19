<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssemblyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mark' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:255',
            'quantity' => 'sometimes|required|integer|min:1',
            'weight_each_lbs' => 'nullable|numeric|min:0',
            'weight_each_kg' => 'nullable|numeric|min:0',
            'assembly_type' => 'nullable|string|max:255',
            'main_member_type' => 'nullable|string|max:255',
            'main_member_size' => 'nullable|string|max:255',
            'main_member_grade' => 'nullable|string|max:255',
            'main_member_length' => 'nullable|numeric|min:0',
            'drawing_id' => 'nullable|exists:drawings,id',
        ];
    }
}
