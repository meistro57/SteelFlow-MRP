<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum JobStatus: string implements HasLabel, HasColor
{
    case Estimating = 'estimating';
    case Awarded = 'awarded';
    case InProgress = 'in_progress';
    case Shipping = 'shipping';
    case Complete = 'complete';
    case Hold = 'hold';

    public function getLabel(): string
    {
        return match ($this) {
            self::Estimating => 'Estimating',
            self::Awarded => 'Awarded',
            self::InProgress => 'In Progress',
            self::Shipping => 'Shipping',
            self::Complete => 'Complete',
            self::Hold => 'On Hold',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Estimating => 'gray',
            self::Awarded => 'info',
            self::InProgress => 'warning',
            self::Shipping => 'primary',
            self::Complete => 'success',
            self::Hold => 'danger',
        };
    }
}
