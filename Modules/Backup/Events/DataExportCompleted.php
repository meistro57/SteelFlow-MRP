<?php

namespace Modules\Backup\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Backup\Models\DataExport;

class DataExportCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public DataExport $export,
    ) {}
}
