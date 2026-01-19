<?php

namespace Modules\NCR\States;

class UnderReview extends NCRState
{
    public static $name = 'under_review';

    public function label(): string
    {
        return 'Under Review';
    }
}
