<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkStockActionRequest extends FormRequest
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
            'item_ids' => ['required', 'array', 'min:1'],
            'item_ids.*' => ['required', 'integer', 'exists:stock_items,id'],
            'action' => ['required', 'string', 'in:transfer,assign,release,commit,use,scrap'],
            'stock_area' => ['required_if:action,transfer', 'nullable', 'string', 'max:50'],
            'project_id' => ['required_if:action,assign', 'nullable', 'exists:projects,id'],
            'reason' => ['required_if:action,scrap', 'nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'item_ids.required' => 'Please select at least one item.',
            'item_ids.min' => 'Please select at least one item.',
            'action.required' => 'Please select an action.',
            'stock_area.required_if' => 'Please select a destination location.',
            'project_id.required_if' => 'Please select a project.',
            'reason.required_if' => 'Please provide a reason for scrapping.',
        ];
    }
}
