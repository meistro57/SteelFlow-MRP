<?php

namespace Modules\NCR\States;

class Created extends NCRState
{
    public static $name = 'created';

    public function label(): string
    {
        return 'Created';
    }
}
