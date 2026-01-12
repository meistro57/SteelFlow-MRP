<?php

namespace Modules\NCR\States;

class Dispositioned extends NCRState
{
    public static $name = 'dispositioned';

    public function label(): string
    {
        return 'Dispositioned';
    }
}
