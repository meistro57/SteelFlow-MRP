<?php

namespace Modules\NCR\States;

class Closed extends NCRState
{
    public static $name = 'closed';

    public function label(): string
    {
        return 'Closed';
    }
}
