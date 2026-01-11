<?php

namespace App\Filament\Resources\AutoHandlingResource\Pages;

use App\Filament\Resources\AutoHandlingResource;
use Filament\Resources\Pages\CreateRecord;
use Modules\UPF\Services\KeyGenerationService;

class CreateAutoHandling extends CreateRecord
{
    protected static string $resource = AutoHandlingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['pkey'])) {
            $data['pkey'] = app(KeyGenerationService::class)->generatePKey('ATH');
        }

        return $data;
    }
}
