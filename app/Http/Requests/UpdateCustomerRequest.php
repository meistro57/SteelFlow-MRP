<?php

// app/Http/Requests/UpdateCustomerRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('customers', 'code')->ignore($this->customer?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'address_1' => ['nullable', 'string', 'max:255'],
            'address_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:50'],
            'zip' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('customers', 'email')->ignore($this->customer?->id),
            ],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }
}
