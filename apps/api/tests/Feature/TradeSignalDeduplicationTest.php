<?php

namespace Tests\Feature;

use App\Models\Symbol;
use App\Models\TradeSignal;
use App\Support\Signals\TradeSignalDeduplicator;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TradeSignalDeduplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_builds_stable_fingerprint_and_setup_key_conventions(): void
    {
        $symbol = $this->makeSymbol('NVDA');
        $deduplicator = new TradeSignalDeduplicator;

        $attributes = [
            'symbol_id' => $symbol->id,
            'strategy_key' => 'trend_continuation',
            'timeframe' => '4h',
            'direction' => 'bullish',
            'bar_time' => '2026-03-30T20:00:00+00:00',
        ];

        $this->assertSame($deduplicator->fingerprint($attributes), $deduplicator->fingerprint($attributes));
        $this->assertSame(''.$symbol->id.':trend_continuation:4h:bullish', $deduplicator->setupKey($attributes));
    }

    public function test_it_prevents_same_bar_duplicates_via_unique_fingerprint(): void
    {
        $symbol = $this->makeSymbol('SPY');
        $deduplicator = new TradeSignalDeduplicator;
        $attributes = [
            'symbol_id' => $symbol->id,
            'strategy_key' => 'trend_continuation',
            'timeframe' => '4h',
            'direction' => 'bullish',
            'bar_time' => '2026-03-30T20:00:00+00:00',
        ];

        TradeSignal::query()->create([
            ...$attributes,
            'signal_category' => 'trend_continuation',
            'thesis' => 'Original signal.',
            'fingerprint' => $deduplicator->fingerprint($attributes),
            'setup_key' => $deduplicator->setupKey($attributes),
        ]);

        $this->expectException(QueryException::class);

        TradeSignal::query()->create([
            ...$attributes,
            'signal_category' => 'trend_continuation',
            'thesis' => 'Duplicate same-bar signal.',
            'fingerprint' => $deduplicator->fingerprint($attributes),
            'setup_key' => $deduplicator->setupKey($attributes),
        ]);
    }

    public function test_it_detects_latest_same_setup_signal_for_replacement_logic(): void
    {
        $symbol = $this->makeSymbol('QQQ');
        $deduplicator = new TradeSignalDeduplicator;

        $first = TradeSignal::query()->create([
            'symbol_id' => $symbol->id,
            'strategy_key' => 'breakout_confirmation',
            'timeframe' => '1d',
            'direction' => 'bullish',
            'signal_category' => 'breakout_confirmation',
            'thesis' => 'First setup.',
            'fingerprint' => sha1('first'),
            'setup_key' => $deduplicator->setupKey([
                'symbol_id' => $symbol->id,
                'strategy_key' => 'breakout_confirmation',
                'timeframe' => '1d',
                'direction' => 'bullish',
            ]),
            'bar_time' => '2026-03-29T20:00:00+00:00',
            'signal_generated_at' => '2026-03-29T20:01:00+00:00',
        ]);

        $second = TradeSignal::query()->create([
            'symbol_id' => $symbol->id,
            'strategy_key' => 'breakout_confirmation',
            'timeframe' => '1d',
            'direction' => 'bullish',
            'signal_category' => 'breakout_confirmation',
            'thesis' => 'Replacement candidate.',
            'fingerprint' => sha1('second'),
            'setup_key' => $first->setup_key,
            'bar_time' => '2026-03-30T20:00:00+00:00',
            'signal_generated_at' => '2026-03-30T20:01:00+00:00',
            'replaces_trade_signal_id' => $first->id,
        ]);

        $latest = $deduplicator->latestSameSetup($first);

        $this->assertNotNull($latest);
        $this->assertTrue($latest->is($second));
        $this->assertSame($first->id, $second->replaces_trade_signal_id);
    }

    private function makeSymbol(string $symbol): Symbol
    {
        return Symbol::query()->create([
            'asset_type' => 'stock',
            'symbol' => $symbol,
            'name' => $symbol.' Test',
            'market' => 'us_equities',
            'provider' => 'manual',
            'provider_symbol' => $symbol,
        ]);
    }
}
