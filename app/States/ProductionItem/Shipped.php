<?php

// app/States/ProductionItem/Shipped.php

declare(strict_types=1);

namespace App\States\ProductionItem;

class Shipped extends ProductionItemState
{
    public function label(): string
    {
        return 'Shipped';
    }
}
