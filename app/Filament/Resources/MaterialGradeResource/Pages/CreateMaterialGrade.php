<?php

namespace App\Filament\Resources\MaterialGradeResource\Pages;

use App\Filament\Resources\MaterialGradeResource;
use Filament\Resources\Pages\CreateRecord;
use Modules\UPF\Services\KeyGenerationService;

class CreateMaterialGrade extends CreateRecord
{
    protected static string $resource = MaterialGradeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['pkey'])) {
            $data['pkey'] = app(KeyGenerationService::class)->generatePKey('GRD');
        }

        return $data;
    }
}
