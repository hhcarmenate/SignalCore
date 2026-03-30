<?php

namespace App\Enums;

enum Timeframe: string
{
    case ThirtyMinutes = '30m';
    case FourHours = '4h';
    case OneDay = '1d';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
