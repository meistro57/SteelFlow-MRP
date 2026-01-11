<?php

namespace App\Filament\Resources\LaborRateResource\Pages;

use App\Filament\Resources\LaborRateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLaborRates extends ListRecords
{
    protected static string $resource = LaborRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
