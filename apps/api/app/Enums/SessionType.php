<?php

namespace App\Enums;

enum SessionType: string
{
    case Regular = 'regular';
    case Extended = 'extended';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
