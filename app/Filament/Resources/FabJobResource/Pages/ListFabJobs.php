<?php

namespace App\Filament\Resources\FabJobResource\Pages;

use App\Filament\Resources\FabJobResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFabJobs extends ListRecords
{
    protected static string $resource = FabJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
