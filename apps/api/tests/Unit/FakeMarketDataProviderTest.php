<?php

namespace Tests\Unit;

use App\Contracts\MarketData\MarketDataProviderInterface;
use App\Data\MarketData\CandleData;
use App\Data\MarketData\QuoteData;
use App\Services\MarketData\FakeMarketDataProvider;
use InvalidArgumentException;
use Tests\TestCase;

class FakeMarketDataProviderTest extends TestCase
{
    public function test_it_resolves_the_fake_provider_through_the_contract(): void
    {
        $provider = $this->app->make(MarketDataProviderInterface::class);

        $this->assertInstanceOf(FakeMarketDataProvider::class, $provider);
    }

    public function test_it_returns_a_normalized_quote_dto(): void
    {
        $provider = new FakeMarketDataProvider();
        $quote = $provider->getQuote('nvda');

        $this->assertInstanceOf(QuoteData::class, $quote);
        $this->assertSame('NVDA', $quote->symbol);
        $this->assertSame('USD', $quote->currency);
        $this->assertGreaterThan(0, $quote->price);
        $this->assertGreaterThan(0, $quote->volume);
        $this->assertArrayHasKey('change_percent', $quote->toArray());
    }

    public function test_it_returns_deterministic_candles_for_supported_timeframes(): void
    {
        $provider = new FakeMarketDataProvider();
        $candles = $provider->getCandles('SPY', '4h', 5);

        $this->assertCount(5, $candles);
        $this->assertContainsOnlyInstancesOf(CandleData::class, $candles);
        $this->assertSame('SPY', $candles[0]->symbol);
        $this->assertSame('4h', $candles[0]->timeframe);
        $this->assertGreaterThan(0, $candles[0]->close);
    }

    public function test_it_rejects_unsupported_fake_timeframes(): void
    {
        $provider = new FakeMarketDataProvider();

        $this->expectException(InvalidArgumentException::class);

        $provider->getCandles('SPY', '15m', 10);
    }
}
