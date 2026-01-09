<?php

namespace App\Filament\Resources\FabJobResource\Pages;

use App\Filament\Resources\FabJobResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewFabJob extends ViewRecord
{
    protected static string $resource = FabJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
