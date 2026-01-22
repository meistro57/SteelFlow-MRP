<?php

// ListMaintenanceEquipmentAssets.php

namespace App\Filament\Resources\MaintenanceEquipmentAssetResource\Pages;

use App\Filament\Resources\MaintenanceEquipmentAssetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMaintenanceEquipmentAssets extends ListRecords
{
    protected static string $resource = MaintenanceEquipmentAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
