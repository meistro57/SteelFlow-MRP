<?php

// app/States/ProductionItem/Welded.php

declare(strict_types=1);

namespace App\States\ProductionItem;

class Welded extends ProductionItemState
{
    public function label(): string
    {
        return 'Welded';
    }
}
