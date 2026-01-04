<?php

// app/Http/Controllers/ProductionController.php

namespace App\Http\Controllers;

use App\Http\Requests\ProcessScanRequest;
use App\Services\Production\ProductionService;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ProductionController extends Controller
{
    protected $productionService;

    public function __construct(ProductionService $productionService)
    {
        $this->productionService = $productionService;
    }

    public function scan()
    {
        return Inertia::render('Production/Scan');
    }

    public function processScan(ProcessScanRequest $request)
    {
        $validated = $request->validated();
        $barcode = $validated['barcode'];

        Log::info('Production barcode scanned', [
            'barcode' => $barcode,
            'scan_type' => $validated['scan_type'] ?? 'unknown',
            'user_id' => $request->user()?->id,
        ]);

        // Logic to parse barcode and update production/inventory
        // For now, just return a success message
        return back()->with('success', 'Processed barcode: '.$barcode);
    }
}
