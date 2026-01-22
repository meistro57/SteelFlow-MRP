<?php

// ListMaintenanceParts.php

namespace App\Filament\Resources\MaintenancePartResource\Pages;

use App\Filament\Resources\MaintenancePartResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMaintenanceParts extends ListRecords
{
    protected static string $resource = MaintenancePartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
