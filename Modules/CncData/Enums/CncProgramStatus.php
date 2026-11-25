<?php

declare(strict_types=1);

namespace Modules\CncData\Enums;

enum CncProgramStatus: string
{
    case Pending = 'pending';     // stored, not analysed yet
    case Analyzed = 'analyzed';   // analysed, no errors
    case Flagged = 'flagged';     // analysed, has error-level checks to look at
    case Approved = 'approved';   // released to the table
    case Cut = 'cut';             // at least one completed run
    case Archived = 'archived';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
