<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum MaterialType: string implements HasLabel
{
    case Plate = 'plate';
    case Beam = 'beam';
    case Channel = 'channel';
    case Angle = 'angle';
    case Tube = 'tube';
    case Pipe = 'pipe';
    case Bar = 'bar';

    public function getLabel(): string
    {
        return match ($this) {
            self::Plate => 'Plate',
            self::Beam => 'Beam (W/S/HP)',
            self::Channel => 'Channel',
            self::Angle => 'Angle',
            self::Tube => 'Tube (HSS)',
            self::Pipe => 'Pipe',
            self::Bar => 'Bar',
        };
    }
}
