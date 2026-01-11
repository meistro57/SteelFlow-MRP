<?php

namespace App\Filament\Resources\MaterialTypeResource\Pages;

use App\Filament\Resources\MaterialTypeResource;
use Filament\Resources\Pages\CreateRecord;
use Modules\UPF\Services\KeyGenerationService;

class CreateMaterialType extends CreateRecord
{
    protected static string $resource = MaterialTypeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['pkey'])) {
            $data['pkey'] = app(KeyGenerationService::class)->generatePKey('MAT');
        }
        
        return $data;
    }
}
