<?php

namespace App\Filament\Resources\FabPartResource\Pages;

use App\Filament\Resources\FabPartResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFabPart extends CreateRecord
{
    protected static string $resource = FabPartResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
