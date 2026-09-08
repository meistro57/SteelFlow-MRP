<?php

namespace Modules\DrawingFlow\Http\Controllers;

use App\Http\Requests\StorePdfMarkupRequest;
use App\Models\DrawingSubmittal;
use App\Models\PdfMarkup;
use App\Models\PdfPageScale;
use App\Models\SubmittalFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubmittalPdfMarkupController extends Controller
{
    public function index(DrawingSubmittal $submittal, SubmittalFile $submittalFile): JsonResponse
    {
        $this->ensureSubmittalFileBelongsToSubmittal($submittal, $submittalFile);
        $this->ensureSubmittalFileIsPdf($submittalFile);

        $markups = $submittalFile->markups()
            ->with('user:id,name')
            ->orderBy('created_at')
            ->get();

        return response()->json(['data' => $markups]);
    }

    public function store(StorePdfMarkupRequest $request, DrawingSubmittal $submittal, SubmittalFile $submittalFile): JsonResponse
    {
        $this->ensureSubmittalFileBelongsToSubmittal($submittal, $submittalFile);
        $this->ensureSubmittalFileIsPdf($submittalFile);

        $markup = PdfMarkup::create([
            'submittal_file_id' => $submittalFile->id,
            'user_id' => $request->user()->id,
            'page_number' => $request->integer('page_number'),
            'markup_type' => $request->string('markup_type')->toString(),
            'markup_data' => $request->validated('markup_data'),
        ])->load('user:id,name');

        return response()->json(['data' => $markup], 201);
    }

    public function update(StorePdfMarkupRequest $request, DrawingSubmittal $submittal, SubmittalFile $submittalFile, PdfMarkup $markup): JsonResponse
    {
        $this->ensureSubmittalFileBelongsToSubmittal($submittal, $submittalFile);
        $this->ensureSubmittalFileIsPdf($submittalFile);
        $this->ensureMarkupBelongsToSubmittalFile($submittalFile, $markup);

        $markup->update([
            'page_number' => $request->integer('page_number'),
            'markup_type' => $request->string('markup_type')->toString(),
            'markup_data' => $request->validated('markup_data'),
        ]);

        return response()->json([
            'data' => $markup->fresh()->load('user:id,name'),
        ]);
    }

    public function scaleIndex(DrawingSubmittal $submittal, SubmittalFile $submittalFile): JsonResponse
    {
        $this->ensureSubmittalFileBelongsToSubmittal($submittal, $submittalFile);
        $this->ensureSubmittalFileIsPdf($submittalFile);

        return response()->json([
            'data' => $submittalFile->pageScales()->orderBy('page_number')->get(),
        ]);
    }

    public function scaleUpsert(Request $request, DrawingSubmittal $submittal, SubmittalFile $submittalFile, int $pageNumber): JsonResponse
    {
        $this->ensureSubmittalFileBelongsToSubmittal($submittal, $submittalFile);
        $this->ensureSubmittalFileIsPdf($submittalFile);

        $validated = $request->validate([
            'calibration_distance' => ['required', 'numeric', 'gt:0'],
            'real_length' => ['required', 'numeric', 'gt:0'],
            'unit' => ['required', 'string', 'max:20'],
        ]);

        $scale = PdfPageScale::updateOrCreate(
            [
                'submittal_file_id' => $submittalFile->id,
                'page_number' => $pageNumber,
            ],
            $validated
        );

        return response()->json([
            'data' => $scale,
        ]);
    }

    public function scaleDestroy(DrawingSubmittal $submittal, SubmittalFile $submittalFile, int $pageNumber): Response
    {
        $this->ensureSubmittalFileBelongsToSubmittal($submittal, $submittalFile);
        $this->ensureSubmittalFileIsPdf($submittalFile);

        $submittalFile->pageScales()->where('page_number', $pageNumber)->delete();

        return response()->noContent();
    }

    public function import(Request $request, DrawingSubmittal $submittal, SubmittalFile $submittalFile): JsonResponse
    {
        $this->ensureSubmittalFileBelongsToSubmittal($submittal, $submittalFile);
        $this->ensureSubmittalFileIsPdf($submittalFile);

        $request->validate([
            'markups_file' => ['required', 'file', 'max:10240'],
        ]);

        $payload = json_decode($request->file('markups_file')->get(), true);

        if (! is_array($payload)) {
            return response()->json([
                'message' => 'The uploaded file must contain valid JSON.',
            ], 422);
        }

        $markups = $payload['markups'] ?? $payload;

        if (! is_array($markups)) {
            return response()->json([
                'message' => 'The uploaded file must contain a markups array.',
            ], 422);
        }

        $validatedMarkups = [];

        foreach ($markups as $index => $markupPayload) {
            $validator = Validator::make($markupPayload, $this->markupRules());

            $validator->after(function ($validator) use ($markupPayload): void {
                $this->validateMarkupPayload($validator, $markupPayload);
            });

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'The uploaded file contains invalid markup data.',
                    'errors' => [
                        "markups.{$index}" => $validator->errors()->all(),
                    ],
                ], 422);
            }

            $validatedMarkups[] = $validator->validated();
        }

        $created = DB::transaction(function () use ($request, $submittalFile, $validatedMarkups) {
            return collect($validatedMarkups)->map(function (array $markupPayload) use ($request, $submittalFile) {
                return PdfMarkup::create([
                    'submittal_file_id' => $submittalFile->id,
                    'user_id' => $request->user()->id,
                    'page_number' => $markupPayload['page_number'],
                    'markup_type' => $markupPayload['markup_type'],
                    'markup_data' => $markupPayload['markup_data'],
                ]);
            });
        });

        return response()->json([
            'imported_count' => $created->count(),
            'data' => $submittalFile->markups()->with('user:id,name')->orderBy('created_at')->get(),
        ], 201);
    }

    public function export(DrawingSubmittal $submittal, SubmittalFile $submittalFile): StreamedResponse
    {
        $this->ensureSubmittalFileBelongsToSubmittal($submittal, $submittalFile);
        $this->ensureSubmittalFileIsPdf($submittalFile);

        $markups = $submittalFile->markups()
            ->with('user:id,name')
            ->orderBy('created_at')
            ->get(['id', 'submittal_file_id', 'user_id', 'page_number', 'markup_type', 'markup_data', 'created_at']);

        $payload = [
            'submittal_id' => $submittal->id,
            'submittal_number' => $submittal->submittal_number,
            'submittal_file_id' => $submittalFile->id,
            'filename' => $submittalFile->original_filename,
            'exported_at' => now()->toIso8601String(),
            'markups' => $markups,
        ];

        return response()->streamDownload(function () use ($payload): void {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }, 'markups-'.$submittalFile->id.'.json', [
            'Content-Type' => 'application/json',
        ]);
    }

    public function destroy(DrawingSubmittal $submittal, SubmittalFile $submittalFile, PdfMarkup $markup): Response
    {
        $this->ensureSubmittalFileBelongsToSubmittal($submittal, $submittalFile);
        $this->ensureSubmittalFileIsPdf($submittalFile);
        $this->ensureMarkupBelongsToSubmittalFile($submittalFile, $markup);

        $markup->delete();

        return response()->noContent();
    }

    private function ensureSubmittalFileBelongsToSubmittal(DrawingSubmittal $submittal, SubmittalFile $submittalFile): void
    {
        if ($submittalFile->submittal_id !== $submittal->id) {
            abort(404);
        }
    }

    private function ensureSubmittalFileIsPdf(SubmittalFile $submittalFile): void
    {
        if (! $submittalFile->isPdf()) {
            abort(404);
        }
    }

    private function ensureMarkupBelongsToSubmittalFile(SubmittalFile $submittalFile, PdfMarkup $markup): void
    {
        if ($markup->submittal_file_id !== $submittalFile->id) {
            abort(404);
        }
    }

    private function markupRules(): array
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

    private function validateMarkupPayload($validator, array $markupPayload): void
    {
        $markupType = $markupPayload['markup_type'] ?? null;
        $markupData = $markupPayload['markup_data'] ?? [];

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
    }
}
