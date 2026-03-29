<?php

namespace App\Services\MarketData;

use App\Contracts\MarketData\MarketDataProviderInterface;
use App\Data\MarketData\QuoteData;

class MarketDataService
{
    public function __construct(private readonly MarketDataProviderInterface $provider)
    {
    }

    public function getQuoteForSymbol(string $symbol): QuoteData
    {
        return $this->provider->getQuote($symbol);
    }

    public function getCandlesForSymbol(string $symbol, string $timeframe, int $limit = 100): array
    {
        return $this->provider->getCandles($symbol, $timeframe, $limit);
    }
}
