<?php

// app/Filament/Resources/ProductionItemResource/Pages/ListProductionItems.php

declare(strict_types=1);

namespace App\Filament\Resources\ProductionItemResource\Pages;

use App\Filament\Resources\ProductionItemResource;
use Filament\Resources\Pages\ListRecords;

class ListProductionItems extends ListRecords
{
    protected static string $resource = ProductionItemResource::class;
}
