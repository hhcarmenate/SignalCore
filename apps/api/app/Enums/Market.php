<?php

namespace App\Enums;

enum Market: string
{
    case UsEquities = 'us_equities';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
