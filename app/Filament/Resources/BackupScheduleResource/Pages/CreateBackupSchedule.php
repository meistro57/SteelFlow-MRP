<?php

namespace App\Filament\Resources\BackupScheduleResource\Pages;

use App\Filament\Resources\BackupScheduleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBackupSchedule extends CreateRecord
{
    protected static string $resource = BackupScheduleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
