<?php

namespace App\Filament\Resources\FabJobResource\Pages;

use App\Filament\Resources\FabJobResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFabJob extends CreateRecord
{
    protected static string $resource = FabJobResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
