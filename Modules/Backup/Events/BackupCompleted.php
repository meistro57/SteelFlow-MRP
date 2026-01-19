<?php

namespace Modules\Backup\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Backup\Models\Backup;

class BackupCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Backup $backup,
    ) {}
}
