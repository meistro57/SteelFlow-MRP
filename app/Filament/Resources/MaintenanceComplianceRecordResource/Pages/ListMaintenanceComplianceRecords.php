<?php

// ListMaintenanceComplianceRecords.php

namespace App\Filament\Resources\MaintenanceComplianceRecordResource\Pages;

use App\Filament\Resources\MaintenanceComplianceRecordResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMaintenanceComplianceRecords extends ListRecords
{
    protected static string $resource = MaintenanceComplianceRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
