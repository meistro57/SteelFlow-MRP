<?php

namespace Modules\NCR\States;

class Open extends NCRState
{
    public static $name = 'open';

    public function label(): string
    {
        return 'Open';
    }
}
