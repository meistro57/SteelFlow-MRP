<?php

namespace Modules\DrawingFlow\Http\Controllers;

use App\Models\DrawingSubmittal;
use App\Models\SubmittalFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SubmittalFileController extends Controller
{
    public function store(Request $request, DrawingSubmittal $submittal): RedirectResponse
    {
        $validated = $request->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['file', 'mimes:pdf', 'max:20480'],
        ]);

        foreach ($validated['files'] as $file) {
            $storedPath = $file->store("submittal-files/{$submittal->id}");

            SubmittalFile::create([
                'submittal_id' => $submittal->id,
                'file_type' => 'drawing',
                'filename' => basename($storedPath),
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $storedPath,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getClientMimeType(),
                'version' => 1,
                'is_current' => true,
                'uploaded_by_user_id' => (int) $request->user()->id,
                'uploaded_at' => now(),
            ]);
        }

        return back()->with('success', 'PDF file(s) uploaded successfully.');
    }

    public function view(DrawingSubmittal $submittal, SubmittalFile $submittalFile): BinaryFileResponse
    {
        $this->ensureSubmittalFileBelongsToSubmittal($submittal, $submittalFile);

        if (! $this->isPdf($submittalFile)) {
            abort(404);
        }

        if (! Storage::disk('local')->exists($submittalFile->file_path)) {
            abort(404);
        }

        return response()->file(
            Storage::disk('local')->path($submittalFile->file_path),
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$submittalFile->original_filename.'"',
            ]
        );
    }

    public function download(DrawingSubmittal $submittal, SubmittalFile $submittalFile): BinaryFileResponse
    {
        $this->ensureSubmittalFileBelongsToSubmittal($submittal, $submittalFile);

        if (! Storage::disk('local')->exists($submittalFile->file_path)) {
            abort(404);
        }

        return response()->download(
            Storage::disk('local')->path($submittalFile->file_path),
            $submittalFile->original_filename
        );
    }

    private function ensureSubmittalFileBelongsToSubmittal(DrawingSubmittal $submittal, SubmittalFile $submittalFile): void
    {
        if ($submittalFile->submittal_id !== $submittal->id) {
            abort(404);
        }
    }

    private function isPdf(SubmittalFile $submittalFile): bool
    {
        return $submittalFile->mime_type === 'application/pdf'
            || str_ends_with(strtolower($submittalFile->original_filename), '.pdf');
    }
}
