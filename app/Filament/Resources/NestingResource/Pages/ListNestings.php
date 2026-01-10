<?php

namespace App\Filament\Resources\NestingResource\Pages;

use App\Filament\Resources\NestingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNestings extends ListRecords
{
    protected static string $resource = NestingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
