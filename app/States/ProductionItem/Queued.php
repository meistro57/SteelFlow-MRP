<?php // app/States/ProductionItem/Queued.php

declare(strict_types=1);

namespace App\States\ProductionItem;

class Queued extends ProductionItemState
{
    public function label(): string
    {
        return 'Queued';
    }
}
