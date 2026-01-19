<?php

namespace Modules\Documents\Services;

use App\Models\Drawing;
use App\Models\Project;
use App\Services\Import\KissImporter;
use Illuminate\Http\UploadedFile;

class CADDrawingService
{
    public function __construct(
        protected KissImporter $kissImporter,
        protected DocumentService $documentService,
    ) {}

    /**
     * Process a unified CAD import (Data file + PDF drawings).
     */
    public function import(UploadedFile $dataFile, Project $project, array $pdfFiles = []): array
    {
        // 1. Store the data file as a document first for record keeping
        $dataDocument = $this->documentService->store($dataFile, [
            'name' => $dataFile->getClientOriginalName(),
            'file_type' => $dataFile->getClientOriginalExtension(),
            'documentable_id' => $project->id,
            'documentable_type' => Project::class,
            'notes' => 'Source data file for CAD import',
        ]);

        // 2. Perform the KISS/XSR import
        // Note: KissImporter currently expects a string path
        $tempPath = $dataFile->getRealPath();
        $importSuccess = $this->kissImporter->import($tempPath, $project);

        if (! $importSuccess) {
            return [
                'success' => false,
                'errors' => $this->kissImporter->getErrors(),
            ];
        }

        // 3. Process PDF drawings if provided
        $matchedDrawings = 0;
        foreach ($pdfFiles as $pdf) {
            $filename = $pdf->getClientOriginalName();
            // Try to extract drawing number from filename (e.g. "100-REV1.pdf" -> "100")
            $drawingNumber = pathinfo($filename, PATHINFO_FILENAME);

            // Clean up common suffixes like -REV1 or (1)
            $drawingNumber = preg_replace('/-REV\d+$/i', '', $drawingNumber);
            $drawingNumber = preg_replace('/\(\d+\)$/', '', $drawingNumber);
            $drawingNumber = trim($drawingNumber);

            $drawing = Drawing::where('project_id', $project->id)
                ->where('number', $drawingNumber)
                ->first();

            if ($drawing) {
                // Attach as a document to the finding
                $this->documentService->store($pdf, [
                    'name' => $filename,
                    'file_type' => 'pdf',
                    'documentable_id' => $drawing->id,
                    'documentable_type' => Drawing::class,
                ]);

                // Also update the legacy file_path on the drawing model for backward compatibility
                $path = $pdf->store('drawings', 'public');
                $drawing->update(['file_path' => $path]);

                $matchedDrawings++;
            }
        }

        return [
            'success' => true,
            'data_document_id' => $dataDocument->id,
            'drawings_matched' => $matchedDrawings,
        ];
    }
}
