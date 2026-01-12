<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'assembly_id' => 'required|exists:assemblies,id',
            'part_mark' => 'required|string|max:255',
            'material_id' => 'nullable|exists:materials,id',
            'upf_price_id' => 'nullable|exists:upf_prices,id',
            'type' => 'nullable|string|max:255',
            'size_imperial' => 'nullable|string|max:255',
            'length' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'is_main_member' => 'boolean',
            'finish' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:255',
        ];
    }
}
