<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportUpfRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\UPF\Models\MaterialType;
use Modules\UPF\Models\UpfPrice;
use Modules\UPF\Services\FabTrolImporter;

class UpfImportController extends Controller
{
    public function __construct(
        protected FabTrolImporter $importer,
    ) {}

    /**
     * Display the UPF import form.
     */
    public function index(): Response
    {
        // Get current UPF catalog stats
        $stats = [
            'material_types' => MaterialType::count(),
            'upf_prices' => UpfPrice::count(),
            'last_import' => UpfPrice::max('updated_at'),
        ];

        return Inertia::render('UPF/Import', [
            'stats' => $stats,
        ]);
    }

    /**
     * Process the UPF import file.
     */
    public function store(ImportUpfRequest $request): RedirectResponse
    {
        $file = $request->file('file');

        // Store temporarily
        $path = $file->store('imports/upf', 'local');
        $fullPath = storage_path('app/'.$path);

        try {
            $results = $this->importer->importUPF($fullPath);

            // Clean up temp file
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            if (! empty($results['errors'])) {
                $errorMessage = sprintf(
                    'Import completed with warnings: %d types, %d grades, %d prices. Errors: %s',
                    $results['types'],
                    $results['grades'],
                    $results['prices'],
                    implode('; ', array_slice($results['errors'], 0, 5)),
                );

                return redirect()
                    ->route('upf-import.index')
                    ->with('warning', $errorMessage);
            }

            $message = sprintf(
                'Import successful: %d material types, %d grades, %d price items imported.',
                $results['types'],
                $results['grades'],
                $results['prices'],
            );

            return redirect()
                ->route('upf-import.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            // Clean up temp file on error
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            return redirect()
                ->route('upf-import.index')
                ->with('error', 'Import failed: '.$e->getMessage());
        }
    }
}
