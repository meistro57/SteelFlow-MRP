<?php

namespace Modules\Documents\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Documents\Models\Document;
use Modules\Documents\Models\DocumentVersion;

class DocumentService
{
    /**
     * Store a new document.
     */
    public function store(UploadedFile $file, array $data = []): Document
    {
        return DB::transaction(function () use ($file, $data) {
            $disk = config('documents.storage_disk', 'public');
            $basePath = config('documents.base_path', 'documents');
            
            $path = $file->store($basePath, $disk);
            
            $document = Document::create([
                'name' => $data['name'] ?? $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $data['file_type'] ?? $file->getClientOriginalExtension(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'version' => 1,
                'metadata' => $data['metadata'] ?? [],
                'documentable_id' => $data['documentable_id'] ?? null,
                'documentable_type' => $data['documentable_type'] ?? null,
                'created_by' => Auth::id(),
            ]);

            DocumentVersion::create([
                'document_id' => $document->id,
                'file_path' => $path,
                'version' => 1,
                'notes' => $data['notes'] ?? 'Initial version',
                'metadata' => $data['metadata'] ?? [],
                'created_by' => Auth::id(),
            ]);

            return $document;
        });
    }

    /**
     * Add a new version to an existing document.
     */
    public function addVersion(Document $document, UploadedFile $file, array $data = []): Document
    {
        return DB::transaction(function () use ($document, $file, $data) {
            $disk = config('documents.storage_disk', 'public');
            $basePath = config('documents.base_path', 'documents');
            
            $path = $file->store($basePath, $disk);
            $newVersionNumber = $document->version + 1;

            DocumentVersion::create([
                'document_id' => $document->id,
                'file_path' => $path,
                'version' => $newVersionNumber,
                'notes' => $data['notes'] ?? null,
                'metadata' => $data['metadata'] ?? [],
                'created_by' => Auth::id(),
            ]);

            $document->update([
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'version' => $newVersionNumber,
                'updated_by' => Auth::id(),
            ]);

            return $document;
        });
    }

    /**
     * Delete a document and all its versions.
     */
    public function delete(Document $document): bool
    {
        return DB::transaction(function () use ($document) {
            $disk = config('documents.storage_disk', 'public');
            
            foreach ($document->versions as $version) {
                Storage::disk($disk)->delete($version->file_path);
            }
            
            $document->versions()->delete();
            return $document->delete();
        });
    }

    /**
     * Get a download response for a specific version or latest.
     */
    public function download(Document $document, ?int $versionNumber = null)
    {
        $disk = config('documents.storage_disk', 'public');
        
        if ($versionNumber) {
            $version = $document->versions()->where('version', $versionNumber)->firstOrFail();
            $path = $version->file_path;
        } else {
            $path = $document->file_path;
        }

        return Storage::disk($disk)->download($path, $document->name);
    }
}
