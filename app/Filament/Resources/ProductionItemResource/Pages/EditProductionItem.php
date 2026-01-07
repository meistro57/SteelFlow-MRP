<?php // app/Filament/Resources/ProductionItemResource/Pages/EditProductionItem.php

declare(strict_types=1);

namespace App\Filament\Resources\ProductionItemResource\Pages;

use App\Filament\Resources\ProductionItemResource;
use Filament\Resources\Pages\EditRecord;

class EditProductionItem extends EditRecord
{
    protected static string $resource = ProductionItemResource::class;
}
