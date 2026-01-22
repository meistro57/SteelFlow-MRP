<?php

// ListMaintenanceEquipmentCategories.php

namespace App\Filament\Resources\MaintenanceEquipmentCategoryResource\Pages;

use App\Filament\Resources\MaintenanceEquipmentCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMaintenanceEquipmentCategories extends ListRecords
{
    protected static string $resource = MaintenanceEquipmentCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
