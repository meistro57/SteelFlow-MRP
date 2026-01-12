<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'po_number' => 'required|string|unique:purchase_orders,po_number|max:255',
            'vendor_id' => 'required|exists:vendors,id',
            'project_id' => 'nullable|exists:projects,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:order_date',
            'ship_to_address' => 'nullable|string',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.material_id' => 'nullable|exists:materials,id',
            'lines.*.type' => 'required|string',
            'lines.*.size' => 'required|string',
            'lines.*.grade' => 'required|string',
            'lines.*.length' => 'required|numeric|min:0',
            'lines.*.quantity' => 'required|numeric|min:0.001',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'freight' => 'nullable|numeric|min:0',
        ];
    }
}
