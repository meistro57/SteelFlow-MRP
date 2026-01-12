<?php

namespace App\Filament\Resources\AuditEntryResource\Pages;

use App\Filament\Resources\AuditEntryResource;
use Filament\Resources\Pages\ListRecords;

class ListAuditEntries extends ListRecords
{
    protected static string $resource = AuditEntryResource::class;
}
