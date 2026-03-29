<?php

namespace App\Support;

class MarketTypeRegistry
{
    public const MAP = [
        'us_equities' => ['stock', 'etf', 'option'],
        'crypto' => ['crypto'],
        'sports' => ['sports_bet'],
        'prediction' => ['prediction_market'],
    ];

    public static function allowedAssetTypes(string $marketType): array
    {
        return self::MAP[$marketType] ?? [];
    }

    public static function values(): array
    {
        return array_keys(self::MAP);
    }

    public static function isCompatible(string $marketType, string $assetType): bool
    {
        return in_array($assetType, self::allowedAssetTypes($marketType), true);
    }
}
