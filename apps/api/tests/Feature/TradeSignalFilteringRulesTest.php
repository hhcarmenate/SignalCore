<?php

namespace Tests\Feature;

use App\Models\Symbol;
use App\Models\TradeSignal;
use App\Support\Signals\TradeSignalFilteringRules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TradeSignalFilteringRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_classifies_and_applies_review_priority(): void
    {
        $signal = $this->makeSignal(symbol: 'NVDA', strategy: 'trend_continuation', direction: 'bullish', timeframe: '4h', score: 88, confidence: 82, rankingScore: 90);

        $updated = app(TradeSignalFilteringRules::class)->apply($signal);

        $this->assertSame('high', $updated->review_priority);
        $this->assertSame('90.00', $updated->review_score);
    }

    public function test_it_filters_review_queue_by_strategy_direction_and_timeframe(): void
    {
        app(TradeSignalFilteringRules::class)->apply($this->makeSignal('NVDA', 'trend_continuation', 'bullish', '4h', 90, 80, 91));
        app(TradeSignalFilteringRules::class)->apply($this->makeSignal('AAPL', 'breakout_confirmation', 'bearish', '1d', 84, 78, 85));
        app(TradeSignalFilteringRules::class)->apply($this->makeSignal('QQQ', 'mean_reversion_to_trend', 'bullish', '15m', 70, 66, 71));

        $queue = app(TradeSignalFilteringRules::class)->reviewQueue([
            'strategy_keys' => ['trend_continuation', 'breakout_confirmation'],
            'directions' => ['bullish'],
            'timeframes' => ['4h'],
        ]);

        $this->assertCount(1, $queue);
        $this->assertSame('NVDA', $queue->first()->symbol->symbol);
    }

    public function test_it_orders_review_queue_by_priority_then_review_score(): void
    {
        $low = app(TradeSignalFilteringRules::class)->apply($this->makeSignal('QQQ', 'trend_continuation', 'bullish', '4h', 60, 55, 60));
        $medium = app(TradeSignalFilteringRules::class)->apply($this->makeSignal('AAPL', 'breakout_confirmation', 'bullish', '4h', 75, 68, 76));
        $high = app(TradeSignalFilteringRules::class)->apply($this->makeSignal('NVDA', 'trend_continuation', 'bullish', '1d', 90, 85, 92));

        $queue = app(TradeSignalFilteringRules::class)->reviewQueue();

        $this->assertSame([$high->id, $medium->id, $low->id], $queue->pluck('id')->all());
    }

    private function makeSignal(string $symbol, string $strategy, string $direction, string $timeframe, int $score, int $confidence, int $rankingScore): TradeSignal
    {
        $symbolModel = Symbol::query()->create([
            'asset_type' => 'stock',
            'symbol' => $symbol,
            'name' => $symbol.' Test',
            'market' => 'us_equities',
            'provider' => 'manual',
            'provider_symbol' => $symbol,
        ]);

        return TradeSignal::query()->create([
            'symbol_id' => $symbolModel->id,
            'strategy_key' => $strategy,
            'timeframe' => $timeframe,
            'direction' => $direction,
            'signal_category' => $strategy,
            'score' => $score,
            'confidence' => $confidence,
            'ranking_score' => $rankingScore,
            'thesis' => 'Filtering/prioritization test signal.',
        ]);
    }
}
