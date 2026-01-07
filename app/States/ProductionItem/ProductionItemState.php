<?php // app/States/ProductionItem/ProductionItemState.php

declare(strict_types=1);

namespace App\States\ProductionItem;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class ProductionItemState extends State
{
    abstract public function label(): string;

    public function next(): ?string
    {
        return match (static::class) {
            Queued::class => Cut::class,
            Cut::class => Welded::class,
            Welded::class => Inspected::class,
            Inspected::class => Shipped::class,
            default => null,
        };
    }

    public static function config(): StateConfig
    {
        return StateConfig::make()
            ->default(Queued::class)
            ->allowTransition(Queued::class, Cut::class)
            ->allowTransition(Cut::class, Welded::class)
            ->allowTransition(Welded::class, Inspected::class)
            ->allowTransition(Inspected::class, Shipped::class);
    }
}
