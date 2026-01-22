<?php

// ListMaintenanceUsageLogs.php

namespace App\Filament\Resources\MaintenanceUsageLogResource\Pages;

use App\Filament\Resources\MaintenanceUsageLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMaintenanceUsageLogs extends ListRecords
{
    protected static string $resource = MaintenanceUsageLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
