<?php

namespace App\Filament\Resources\UpfPriceResource\Pages;

use App\Filament\Resources\UpfPriceResource;
use Filament\Resources\Pages\CreateRecord;
use Modules\UPF\Services\KeyGenerationService;

class CreateUpfPrice extends CreateRecord
{
    protected static string $resource = UpfPriceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $keyGen = app(KeyGenerationService::class);

        if (empty($data['pkey'])) {
            $data['pkey'] = $keyGen->generatePKey('UPF');
        }

        if (empty($data['filekey'])) {
            $data['filekey'] = $keyGen->getNextFileKey();
        }

        if (empty($data['orderkey'])) {
            $data['orderkey'] = $keyGen->getNextOrderKey();
        }

        return $data;
    }
}
