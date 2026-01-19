<?php

namespace App\Filament\Resources\BackupScheduleResource\Pages;

use App\Filament\Resources\BackupScheduleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBackupSchedule extends ViewRecord
{
    protected static string $resource = BackupScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
