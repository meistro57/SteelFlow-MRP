<?php

namespace App\Filament\Resources\FabPartResource\Pages;

use App\Filament\Resources\FabPartResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFabPart extends EditRecord
{
    protected static string $resource = FabPartResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
