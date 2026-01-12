<?php

namespace Modules\PdfCenter\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Modules\Documents\Services\DocumentService;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;

class PdfService
{
    public function __construct(
        protected DocumentService $documentService
    ) {}

    /**
     * Generate a PDF from a view and return the stream.
     */
    public function generate(string $view, array $data = [], array $options = [])
    {
        return Pdf::loadView($view, $data)
            ->setPaper($options['paper'] ?? 'a4', $options['orientation'] ?? 'portrait');
    }

    /**
     * Generate and save PDF to the Documents module.
     */
    public function generateAndSave(string $view, array $data, string $filename, ?array $documentData = [])
    {
        $pdf = $this->generate($view, $data);
        $output = $pdf->output();
        
        $tempPath = tempnam(sys_get_temp_dir(), 'pdf_');
        file_put_contents($tempPath, $output);
        
        // Create an UploadedFile instance from the temp file
        $file = new UploadedFile(
            $tempPath,
            $filename,
            'application/pdf',
            null,
            true
        );

        $document = $this->documentService->store($file, array_merge([
            'name' => $filename,
            'file_type' => 'pdf',
        ], $documentData));

        unlink($tempPath);

        return $document;
    }
}
