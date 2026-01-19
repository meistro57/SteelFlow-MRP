<?php

// app/States/ProductionItem/Inspected.php

declare(strict_types=1);

namespace App\States\ProductionItem;

class Inspected extends ProductionItemState
{
    public function label(): string
    {
        return 'Inspected';
    }
}
