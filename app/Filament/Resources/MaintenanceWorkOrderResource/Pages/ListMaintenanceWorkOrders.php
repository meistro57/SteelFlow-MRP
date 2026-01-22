<?php

// ListMaintenanceWorkOrders.php

namespace App\Filament\Resources\MaintenanceWorkOrderResource\Pages;

use App\Filament\Resources\MaintenanceWorkOrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMaintenanceWorkOrders extends ListRecords
{
    protected static string $resource = MaintenanceWorkOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
