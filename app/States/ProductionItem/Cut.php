<?php // app/States/ProductionItem/Cut.php

declare(strict_types=1);

namespace App\States\ProductionItem;

class Cut extends ProductionItemState
{
    public function label(): string
    {
        return 'Cut';
    }
}
