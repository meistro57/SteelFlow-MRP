<?php

namespace App\Filament\Resources\NestingResource\Pages;

use App\Filament\Resources\NestingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNesting extends EditRecord
{
    protected static string $resource = NestingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
