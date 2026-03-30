<?php

namespace App\Enums;

enum AssetType: string
{
    case Stock = 'stock';
    case Etf = 'etf';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
