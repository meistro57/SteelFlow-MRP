<?php

namespace App\Filament\Resources\MaterialGradeResource\Pages;

use App\Filament\Resources\MaterialGradeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMaterialGrades extends ListRecords
{
    protected static string $resource = MaterialGradeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
