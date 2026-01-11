<?php

namespace App\Filament\Resources\UpfPriceResource\Pages;

use App\Filament\Resources\UpfPriceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUpfPrices extends ListRecords
{
    protected static string $resource = UpfPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
