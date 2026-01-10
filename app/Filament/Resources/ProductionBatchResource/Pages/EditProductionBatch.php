<?php

namespace App\Filament\Resources\ProductionBatchResource\Pages;

use App\Filament\Resources\ProductionBatchResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProductionBatch extends EditRecord
{
    protected static string $resource = ProductionBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
