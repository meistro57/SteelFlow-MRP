<?php

namespace Modules\NCR\States;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class NCRState extends State
{
    abstract public function label(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Open::class)
            ->allowTransition(Open::class, UnderReview::class)
            ->allowTransition(UnderReview::class, Dispositioned::class)
            ->allowTransition(Dispositioned::class, Closed::class)
            ->allowTransition(UnderReview::class, Closed::class); // Fallback for cancelled or immediate close
    }
}
