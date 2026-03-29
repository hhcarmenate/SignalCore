<?php

namespace Tests\Unit;

use App\Data\MarketData\CandleData;
use App\Data\MarketData\QuoteData;
use App\Services\MarketData\MarketDataService;
use Tests\TestCase;

class MarketDataServiceTest extends TestCase
{
    public function test_it_resolves_from_the_container(): void
    {
        $service = $this->app->make(MarketDataService::class);

        $this->assertInstanceOf(MarketDataService::class, $service);
    }

    public function test_it_returns_a_quote_for_a_symbol(): void
    {
        $service = $this->app->make(MarketDataService::class);
        $quote = $service->getQuoteForSymbol('QQQ');

        $this->assertInstanceOf(QuoteData::class, $quote);
        $this->assertSame('QQQ', $quote->symbol);
        $this->assertGreaterThan(0, $quote->price);
    }

    public function test_it_returns_candles_for_a_symbol_and_timeframe(): void
    {
        $service = $this->app->make(MarketDataService::class);
        $candles = $service->getCandlesForSymbol('SPY', 'daily', 3);

        $this->assertCount(3, $candles);
        $this->assertContainsOnlyInstancesOf(CandleData::class, $candles);
        $this->assertSame('SPY', $candles[0]->symbol);
        $this->assertSame('daily', $candles[0]->timeframe);
    }
}
