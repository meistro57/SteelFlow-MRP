<?php

namespace App\Filament\Resources\AssemblyResource\Pages;

use App\Filament\Resources\AssemblyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAssemblies extends ListRecords
{
    protected static string $resource = AssemblyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
