<?php

namespace Tests\Feature;

use App\Models\ScannerStrategy;
use App\Models\Symbol;
use App\Support\Signals\TradeSignalPersistenceContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class TradeSignalPersistenceContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_maps_and_persists_scanner_payloads_into_trade_signals(): void
    {
        Symbol::query()->create([
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

        $signal = app(TradeSignalPersistenceContract::class)->persist([
            'symbol' => 'NVDA',
            'strategy_key' => 'trend_continuation',
            'timeframe' => '4h',
            'direction' => 'bullish',
            'execution_hint' => 'call',
            'signal_category' => 'trend_continuation',
            'thesis' => 'Scanner payload mapping test.',
            'score' => 82.5,
            'confidence' => 79.2,
            'ranking_score' => 84.1,
            'ranking_position' => 1,
            'levels' => [
                'entry' => 100.5,
                'stop_loss' => 95.1,
                'target' => 112.9,
            ],
            'score_breakdown' => ['composite' => 82.5],
            'indicators' => ['ema_20' => 98.2],
            'context' => ['trend_bias' => 'bullish'],
            'metadata' => ['origin' => 'scanner'],
            'source_run_reference' => 'run-2026-03-30-001',
            'source_signal_reference' => 'signal-abc-123',
        ]);

        $this->assertSame($strategy->id, $signal->scanner_strategy_id);
        $this->assertSame('call', $signal->execution_hint);
        $this->assertSame('82.50', $signal->score);
        $this->assertSame('79.20', $signal->confidence);
        $this->assertSame('run-2026-03-30-001', $signal->source_run_reference);
        $this->assertSame('signal-abc-123', $signal->source_signal_reference);
        $this->assertSame('scanner', $signal->metadata['origin']);
    }

    public function test_it_rejects_payloads_missing_required_fields(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(TradeSignalPersistenceContract::class)->persist([
            'symbol' => 'NVDA',
            'timeframe' => '4h',
        ]);
    }

    public function test_it_rejects_payloads_without_a_matching_symbol(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(TradeSignalPersistenceContract::class)->persist([
            'symbol' => 'MISSING',
            'strategy_key' => 'trend_continuation',
            'timeframe' => '4h',
            'direction' => 'bullish',
            'signal_category' => 'trend_continuation',
            'thesis' => 'No matching symbol.',
        ]);
    }
}
