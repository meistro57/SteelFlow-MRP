<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\StockItem;
use App\Services\LabelService;
use Illuminate\Http\Request;

class LabelController extends Controller
{
    protected $labelService;

    public function __construct(LabelService $labelService)
    {
        $this->labelService = $labelService;
    }

    public function part(Part $part)
    {
        $zpl = $this->labelService->generatePartZpl($part);
        return response($zpl)->header('Content-Type', 'text/plain');
    }

    public function stock(StockItem $item)
    {
        $zpl = $this->labelService->generateStockZpl($item);
        return response($zpl)->header('Content-Type', 'text/plain');
    }
}
