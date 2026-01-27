<?php

namespace Modules\NCR\States;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class NCRState extends State
{
    abstract public function label(): string;

    public function getName(): string
    {
        return (string) $this->getValue();
    }

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Open::class)
            // Legacy/test workflow states:
            ->allowTransition(Created::class, Reported::class)
            ->allowTransition(Reported::class, Dispositioned::class)
            ->allowTransition(Reported::class, Closed::class)
            ->allowTransition(Created::class, Closed::class)
            // Current workflow states:
            ->allowTransition(Open::class, UnderReview::class)
            ->allowTransition(UnderReview::class, Dispositioned::class)
            ->allowTransition(Dispositioned::class, Closed::class)
            ->allowTransition(UnderReview::class, Closed::class); // Fallback for cancelled or immediate close
    }
}
