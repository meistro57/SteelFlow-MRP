<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockItemRequest extends FormRequest
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
            'material_id' => ['nullable', 'exists:materials,id'],
            'type' => ['required', 'string', 'max:50'],
            'size' => ['required', 'string', 'max:50'],
            'grade' => ['required', 'string', 'max:50'],
            'length' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'string', 'in:free,assigned,used,scrapped'],
            'reserved_project_id' => ['nullable', 'exists:projects,id'],
            'stock_area' => ['nullable', 'string', 'max:50'],
            'heat_number' => ['nullable', 'string', 'max:100'],
            'po_number' => ['nullable', 'string', 'max:100'],
            'country_of_origin' => ['nullable', 'string', 'max:100'],
            'cost_per_unit' => ['nullable', 'numeric', 'min:0'],
            'receive_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Material type is required.',
            'size.required' => 'Material size is required.',
            'grade.required' => 'Material grade is required.',
            'length.required' => 'Length is required.',
            'quantity.required' => 'Quantity is required.',
            'quantity.min' => 'Quantity must be at least 1.',
            'status.in' => 'Invalid status.',
        ];
    }
}
