<?php

namespace App\Filament\Resources\LaborRateResource\Pages;

use App\Filament\Resources\LaborRateResource;
use Filament\Resources\Pages\CreateRecord;
use Modules\UPF\Services\KeyGenerationService;

class CreateLaborRate extends CreateRecord
{
    protected static string $resource = LaborRateResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['pkey'])) {
            $data['pkey'] = app(KeyGenerationService::class)->generatePKey('LAB');
        }
        return $data;
    }
}
