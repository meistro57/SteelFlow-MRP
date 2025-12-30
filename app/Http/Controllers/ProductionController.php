<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ProductionService;
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

    public function processScan(Request $request)
    {
        $barcode = $request->input('barcode');
        // Logic to parse barcode and update production/inventory
        // For now, just return a success message
        return back()->with('success', 'Processed barcode: ' . $barcode);
    }
}
