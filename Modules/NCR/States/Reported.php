<?php

namespace Modules\NCR\States;

class Reported extends NCRState
{
    public static $name = 'reported';

    public function label(): string
    {
        return 'Reported';
    }
}
