<?php

namespace App\Contracts\MarketData;

use App\Data\MarketData\CandleData;
use App\Data\MarketData\QuoteData;

interface MarketDataProviderInterface
{
    public function getQuote(string $symbol): QuoteData;

    /**
     * @return array<int, CandleData>
     */
    public function getCandles(string $symbol, string $timeframe, int $limit = 100): array;
}
