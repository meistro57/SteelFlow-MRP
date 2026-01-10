<?php

namespace App\Filament\Resources\DataExportResource\Pages;

use App\Filament\Resources\DataExportResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDataExport extends CreateRecord
{
    protected static string $resource = DataExportResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['status'] = 'pending';

        return $data;
    }
}
