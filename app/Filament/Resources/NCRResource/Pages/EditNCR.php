<?php

namespace App\Filament\Resources\NCRResource\Pages;

use App\Filament\Resources\NCRResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNCR extends EditRecord
{
    protected static string $resource = NCRResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
