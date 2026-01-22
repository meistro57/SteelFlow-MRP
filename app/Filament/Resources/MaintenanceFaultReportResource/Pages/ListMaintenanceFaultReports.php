<?php

// ListMaintenanceFaultReports.php

namespace App\Filament\Resources\MaintenanceFaultReportResource\Pages;

use App\Filament\Resources\MaintenanceFaultReportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMaintenanceFaultReports extends ListRecords
{
    protected static string $resource = MaintenanceFaultReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
