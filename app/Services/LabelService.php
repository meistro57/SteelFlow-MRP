<?php

namespace App\Services;

use App\Models\Part;
use App\Models\StockItem;

class LabelService
{
    /**
     * Generate ZPL code for a Part label
     */
    public function generatePartZpl(Part $part): string
    {
        $zpl = "^XA\n";
        $zpl .= '^CF0,60\^FO50,50^FDPart: '.$part->part_mark."^FS\n";
        $zpl .= "^CF0,30\^FO50,120^FDProject: ".($part->project->name ?? 'N/A')."^FS\n";
        $zpl .= '^FO50,160^FDWeight: '.round($part->weight_each_lbs ?? 0, 2)." lbs^FS\n";
        $zpl .= '^BY3,2,100^FO50,220^BCN,100,Y,N,N^FD'.$part->id."^FS\n";
        $zpl .= '^XZ';

        return $zpl;
    }

    /**
     * Generate ZPL code for an Inventory/Stock item label
     */
    public function generateStockZpl(StockItem $item): string
    {
        $zpl = "^XA\n";
        $zpl .= '^CF0,60\^FO50,50^FDStock: '.$item->grade.' '.$item->type."^FS\n";
        $zpl .= '^CF0,30\^FO50,120^FDLength: '.$item->length." in^FS\n";
        $zpl .= '^FO50,160^FDHeat #: '.($item->heat_number ?: 'N/A')."^FS\n";
        $zpl .= '^BY3,2,100^FO50,220^BCN,100,Y,N,N^FD'.$item->id."^FS\n";
        $zpl .= '^XZ';

        return $zpl;
    }
}
