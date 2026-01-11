<?php

namespace Modules\Backup\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Backup\Models\DataExport;

class DataExportFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public DataExport $export,
        public string $errorMessage,
    ) {}
}
