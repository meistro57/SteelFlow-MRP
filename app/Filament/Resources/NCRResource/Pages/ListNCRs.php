<?php

namespace App\Filament\Resources\NCRResource\Pages;

use App\Filament\Resources\NCRResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNCRs extends ListRecords
{
    protected static string $resource = NCRResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
