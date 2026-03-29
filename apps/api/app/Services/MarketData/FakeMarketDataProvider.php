<?php

namespace App\Services\MarketData;

use App\Contracts\MarketData\MarketDataProviderInterface;
use App\Data\MarketData\CandleData;
use App\Data\MarketData\QuoteData;
use InvalidArgumentException;

class FakeMarketDataProvider implements MarketDataProviderInterface
{
    public function getQuote(string $symbol): QuoteData
    {
        $symbol = strtoupper(trim($symbol));
        $seed = $this->symbolSeed($symbol);
        $previousClose = round(80 + ($seed % 220) + (($seed % 100) / 100), 2);
        $change = round(((($seed % 17) - 8) * 0.41), 2);
        $price = round($previousClose + $change, 2);
        $open = round($previousClose + ($change * 0.25), 2);
        $high = round(max($price, $open) + 1.85, 2);
        $low = round(min($price, $open) - 1.65, 2);
        $changePercent = round(($change / $previousClose) * 100, 2);
        $volume = 1000000 + (($seed % 400) * 25000);

        return new QuoteData(
            symbol: $symbol,
            price: $price,
            change: $change,
            changePercent: $changePercent,
            open: $open,
            high: $high,
            low: $low,
            previousClose: $previousClose,
            volume: $volume,
            currency: 'USD',
            asOf: now()->utc()->toIso8601String(),
        );
    }

    public function getCandles(string $symbol, string $timeframe, int $limit = 100): array
    {
        $symbol = strtoupper(trim($symbol));
        $timeframe = strtolower(trim($timeframe));
        $limit = max(1, min($limit, 500));
        $stepHours = match ($timeframe) {
            '4h' => 4,
            '1d', 'daily' => 24,
            default => throw new InvalidArgumentException("Unsupported fake timeframe [{$timeframe}]."),
        };

        $seed = $this->symbolSeed($symbol);
        $base = 80 + ($seed % 220) + (($seed % 100) / 100);
        $candles = [];

        for ($index = $limit - 1; $index >= 0; $index--) {
            $wave = sin(($seed + $index) / 3.5) * 1.8;
            $drift = ($limit - $index) * 0.37;
            $open = round($base + $drift + $wave, 2);
            $close = round($open + cos(($seed + $index) / 2.8) * 1.15, 2);
            $high = round(max($open, $close) + 1.35, 2);
            $low = round(min($open, $close) - 1.15, 2);
            $volume = 900000 + (($seed + ($index * 17)) % 500) * 18000;
            $timestamp = now()->utc()->subHours($index * $stepHours)->startOfHour()->toIso8601String();

            $candles[] = new CandleData(
                symbol: $symbol,
                timeframe: $timeframe,
                timestamp: $timestamp,
                open: $open,
                high: $high,
                low: $low,
                close: $close,
                volume: $volume,
            );
        }

        return $candles;
    }

    private function symbolSeed(string $symbol): int
    {
        return array_sum(array_map('ord', str_split($symbol)));
    }
}
