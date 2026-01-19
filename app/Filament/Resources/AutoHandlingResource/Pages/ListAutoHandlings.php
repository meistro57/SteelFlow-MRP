<?php

namespace App\Filament\Resources\AutoHandlingResource\Pages;

use App\Filament\Resources\AutoHandlingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAutoHandlings extends ListRecords
{
    protected static string $resource = AutoHandlingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
