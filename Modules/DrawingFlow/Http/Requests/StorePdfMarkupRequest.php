<?php

namespace Modules\DrawingFlow\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePdfMarkupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'page_number' => ['required', 'integer', 'min:1'],
            'markup_type' => ['required', 'in:circle,arrow,text,highlight,stamp,dimension,rectangle,cloud,pen,polyline,polygon'],
            'markup_data' => ['required', 'array'],
            'markup_data.x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'markup_data.y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'markup_data.x2' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'markup_data.y2' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'markup_data.width' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'markup_data.height' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'markup_data.points' => ['nullable', 'array', 'min:2'],
            'markup_data.points.*.x' => ['required_with:markup_data.points', 'numeric', 'min:0', 'max:100'],
            'markup_data.points.*.y' => ['required_with:markup_data.points', 'numeric', 'min:0', 'max:100'],
            'markup_data.text' => ['nullable', 'string', 'max:500'],
            'markup_data.label' => ['nullable', 'string', 'max:100'],
            'markup_data.comment' => ['nullable', 'string', 'max:500'],
            'markup_data.color' => ['nullable', 'string', 'max:20'],
            'markup_data.bg_color' => ['nullable', 'string', 'max:20'],
            'markup_data.border_color' => ['nullable', 'string', 'max:20'],
            'markup_data.stroke_width' => ['nullable', 'numeric', 'min:0.2', 'max:8'],
            'markup_data.opacity' => ['nullable', 'numeric', 'min:0.05', 'max:1'],
            'markup_data.font_size' => ['nullable', 'numeric', 'min:1', 'max:8'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $markupType = $this->input('markup_type');
            $markupData = $this->input('markup_data', []);

            $requiredFields = match ($markupType) {
                'circle', 'highlight', 'rectangle', 'cloud' => ['x', 'y', 'width', 'height'],
                'arrow', 'dimension' => ['x', 'y', 'x2', 'y2'],
                'text' => ['x', 'y', 'text'],
                'stamp' => ['x', 'y', 'label'],
                'pen', 'polyline', 'polygon' => ['points'],
                default => [],
            };

            foreach ($requiredFields as $field) {
                if (! array_key_exists($field, $markupData) || $markupData[$field] === null || $markupData[$field] === '') {
                    $validator->errors()->add("markup_data.{$field}", "The markup_data.{$field} field is required for {$markupType} markups.");
                }
            }

            if (in_array($markupType, ['pen', 'polyline'], true) && count($markupData['points'] ?? []) < 2) {
                $validator->errors()->add('markup_data.points', "The markup_data.points field must contain at least 2 points for {$markupType} markups.");
            }

            if ($markupType === 'polygon' && count($markupData['points'] ?? []) < 3) {
                $validator->errors()->add('markup_data.points', 'The markup_data.points field must contain at least 3 points for polygon markups.');
            }
        });
    }
}
