<?php

declare(strict_types=1);

namespace Modules\CncData\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCncProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            // Send a file, or the program text plus a name.
            'file' => ['required_without:text', 'file', 'max:20480'],
            'text' => ['required_without:file', 'string', 'max:20971520'],
            'program_name' => ['required_with:text', 'string', 'max:255'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'machine_id' => ['nullable', 'integer', 'exists:machines,id'],
            'rapid_rate' => ['nullable', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array{0:string,1:string} [contents, program name] */
    public function program(): array
    {
        if ($this->hasFile('file')) {
            return [(string) $this->file('file')->get(), $this->file('file')->getClientOriginalName()];
        }

        return [(string) $this->input('text'), (string) $this->input('program_name')];
    }
}
