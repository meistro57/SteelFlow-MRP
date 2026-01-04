<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\StockItem;
use App\Services\LabelService;

class LabelController extends Controller
{
    protected $labelService;

    public function __construct(LabelService $labelService)
    {
        $this->labelService = $labelService;
    }

    public function part(Part $part)
    {
        // Eager load project to prevent N+1 query in LabelService
        $part->load('project');

        $zpl = $this->labelService->generatePartZpl($part);

        return response($zpl)->header('Content-Type', 'text/plain');
    }

    public function stock(StockItem $item)
    {
        $zpl = $this->labelService->generateStockZpl($item);

        return response($zpl)->header('Content-Type', 'text/plain');
    }
}
