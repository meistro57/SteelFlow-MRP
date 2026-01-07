<?php

// app/Filament/Resources/ProductionItemResource/Pages/CreateProductionItem.php

declare(strict_types=1);

namespace App\Filament\Resources\ProductionItemResource\Pages;

use App\Filament\Resources\ProductionItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductionItem extends CreateRecord
{
    protected static string $resource = ProductionItemResource::class;
}
