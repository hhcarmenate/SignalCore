<?php

namespace Tests\Feature;

use App\Models\ScannerStrategy;
use App\Models\Symbol;
use App\Models\TradeSignal;
use App\Models\Watchlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TradeSignalSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_persists_trade_signals_with_required_signal_fields(): void
    {
        $watchlist = Watchlist::query()->create([
            'name' => 'Momentum Signals',
            'market_type' => 'us_equities',
            'is_active' => true,
        ]);

        $symbol = Symbol::query()->create([
            'asset_type' => 'stock',
            'symbol' => 'NVDA',
            'name' => 'NVIDIA Corporation',
            'market' => 'us_equities',
            'provider' => 'manual',
            'provider_symbol' => 'NVDA',
        ]);

        $strategy = ScannerStrategy::query()->create([
            'key' => 'trend_continuation',
            'name' => 'Trend Continuation',
            'description' => 'Core strategy',
            'is_enabled' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $signal = TradeSignal::query()->create([
            'watchlist_id' => $watchlist->id,
            'symbol_id' => $symbol->id,
            'scanner_strategy_id' => $strategy->id,
            'strategy_key' => 'trend_continuation',
            'timeframe' => '4h',
            'direction' => 'bullish',
            'execution_hint' => 'call',
            'signal_category' => 'trend_continuation',
            'status' => 'new',
            'entry_price' => 100.50,
            'stop_loss' => 95.25,
            'target_price' => 112.75,
            'score' => 82.5,
            'confidence' => 79.25,
            'ranking_score' => 84.0,
            'ranking_position' => 1,
            'thesis' => 'Trend continuation setup confirmed with strong alignment.',
            'score_breakdown' => ['trend_alignment' => 27.0, 'composite' => 82.5],
            'indicator_snapshot' => ['ema_20' => 98.2, 'rsi_14' => 62.4],
            'market_context' => ['trend_bias' => 'bullish', 'regime' => 'trend'],
            'metadata' => ['source' => 'scanner'],
            'signal_generated_at' => '2026-03-30T20:30:00+00:00',
            'expires_at' => '2026-03-31T20:30:00+00:00',
        ]);

        $this->assertSame('82.50', $signal->score);
        $this->assertSame('79.25', $signal->confidence);
        $this->assertSame('84.00', $signal->ranking_score);
        $this->assertSame('call', $signal->execution_hint);
        $this->assertSame('bullish', $signal->direction);
        $this->assertSame('scanner', $signal->metadata['source']);
        $this->assertSame('bullish', $signal->market_context['trend_bias']);
    }

    public function test_it_exposes_expected_signal_relationships(): void
    {
        $signal = new TradeSignal;

        $this->assertSame('watchlist', $signal->watchlist()->getRelationName());
        $this->assertSame('symbol', $signal->symbol()->getRelationName());
        $this->assertSame('scannerStrategy', $signal->scannerStrategy()->getRelationName());
    }
}
