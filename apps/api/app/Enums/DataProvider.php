<?php

namespace App\Enums;

enum DataProvider: string
{
    case TwelveData = 'twelve_data';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
