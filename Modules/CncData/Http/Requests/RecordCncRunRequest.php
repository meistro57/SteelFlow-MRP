<?php

declare(strict_types=1);

namespace Modules\CncData\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecordCncRunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'actual_minutes' => ['required', 'numeric', 'gt:0', 'max:10000'],
            'machine_id' => ['nullable', 'integer', 'exists:machines,id'],
            'operator_id' => ['nullable', 'integer', 'exists:users,id'],
            'started_at' => ['nullable', 'date'],
            'finished_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'arc_on_minutes' => ['nullable', 'numeric', 'gte:0'],
            'pierce_count' => ['nullable', 'integer', 'gte:0'],
            'result' => ['nullable', 'in:complete,partial,aborted'],
            'source' => ['nullable', 'in:manual,phoenix_log,xpr,time_entry'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
