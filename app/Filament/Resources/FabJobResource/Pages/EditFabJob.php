<?php

namespace App\Filament\Resources\FabJobResource\Pages;

use App\Filament\Resources\FabJobResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFabJob extends EditRecord
{
    protected static string $resource = FabJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
