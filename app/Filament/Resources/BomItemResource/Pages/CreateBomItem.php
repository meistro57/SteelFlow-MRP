<?php

namespace App\Filament\Resources\BomItemResource\Pages;

use App\Filament\Resources\BomItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBomItem extends CreateRecord
{
    protected static string $resource = BomItemResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
