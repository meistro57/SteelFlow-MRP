<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PartStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Nested = 'nested';
    case Cutting = 'cutting';
    case Fabrication = 'fabrication';
    case Complete = 'complete';
    case Shipped = 'shipped';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Nested => 'Nested',
            self::Cutting => 'Cutting',
            self::Fabrication => 'Fabrication',
            self::Complete => 'Complete',
            self::Shipped => 'Shipped',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Nested => 'info',
            self::Cutting => 'warning',
            self::Fabrication => 'primary',
            self::Complete => 'success',
            self::Shipped => 'success',
        };
    }
}
