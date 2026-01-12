<?php

namespace App\Filament\Resources\NCRResource\Pages;

use App\Filament\Resources\NCRResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateNCR extends CreateRecord
{
    protected static string $resource = NCRResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['reported_by'] = Auth::id();
        return $data;
    }
}
