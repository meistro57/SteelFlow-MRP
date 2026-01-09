<?php

namespace App\Filament\Resources\FabPartResource\Pages;

use App\Filament\Resources\FabPartResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFabParts extends ListRecords
{
    protected static string $resource = FabPartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
