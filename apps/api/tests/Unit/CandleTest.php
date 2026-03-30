<?php

namespace Tests\Unit;

use App\Enums\DataProvider;
use App\Models\Candle;
use App\Models\Symbol;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CandleTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_casts_candle_attributes(): void
    {
        $symbol = Symbol::query()->create([
            'asset_type' => 'stock',
            'symbol' => 'SPY',
            'name' => 'SPDR S&P 500 ETF Trust',
            'market' => 'us_equities',
            'provider' => 'manual',
            'provider_symbol' => 'SPY',
        ]);

        $candle = Candle::query()->create([
            'symbol_id' => $symbol->id,
            'timeframe' => '4h',
            'bar_time' => '2026-03-30T12:00:00+00:00',
            'open' => 560.125,
            'high' => 563.5,
            'low' => 559.75,
            'close' => 562.875,
            'volume' => 1234567,
            'provider' => DataProvider::TwelveData->value,
            'vwap' => 561.3475,
            'trade_count' => 4567,
            'session_type' => 'regular',
            'is_final' => false,
        ]);

        $this->assertInstanceOf(CarbonImmutable::class, $candle->bar_time);
        $this->assertSame('560.12500000', $candle->open);
        $this->assertSame('563.50000000', $candle->high);
        $this->assertSame('559.75000000', $candle->low);
        $this->assertSame('562.87500000', $candle->close);
        $this->assertSame('561.34750000', $candle->vwap);
        $this->assertSame(1234567, $candle->volume);
        $this->assertSame(4567, $candle->trade_count);
        $this->assertFalse($candle->is_final);
    }

    public function test_it_applies_model_defaults(): void
    {
        $symbol = Symbol::query()->create([
            'asset_type' => 'stock',
            'symbol' => 'QQQ',
            'name' => 'Invesco QQQ Trust',
            'market' => 'us_equities',
            'provider' => 'manual',
            'provider_symbol' => 'QQQ',
        ]);

        $candle = Candle::query()->create([
            'symbol_id' => $symbol->id,
            'timeframe' => '1d',
            'bar_time' => '2026-03-30T00:00:00+00:00',
            'open' => 500,
            'high' => 505,
            'low' => 498,
            'close' => 503,
            'volume' => 987654,
        ]);

        $this->assertSame(DataProvider::TwelveData->value, $candle->provider);
        $this->assertTrue($candle->is_final);
    }

    public function test_it_exposes_the_symbol_relationship(): void
    {
        $relation = (new Candle)->symbol();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Symbol::class, $relation->getRelated());
    }
}
