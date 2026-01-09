<?php

namespace App\Filament\Resources\BomItemResource\Pages;

use App\Filament\Resources\BomItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBomItems extends ListRecords
{
    protected static string $resource = BomItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
