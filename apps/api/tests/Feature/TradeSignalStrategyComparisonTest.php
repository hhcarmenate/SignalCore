<?php

namespace Tests\Feature;

use App\Enums\TradeSignalOutcomeLabel;
use App\Enums\TradeSignalOutcomeState;
use App\Models\Symbol;
use App\Models\TradeSignal;
use App\Models\TradeSignalOutcome;
use App\Support\Signals\TradeSignalStrategyComparison;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TradeSignalStrategyComparisonTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_builds_a_strategy_leaderboard_with_sample_size_notes(): void
    {
        $this->makeOutcome('trend_continuation', 'NVDA', '4h', 'bullish', TradeSignalOutcomeState::TargetHit, TradeSignalOutcomeLabel::Win);
        $this->makeOutcome('trend_continuation', 'AAPL', '1d', 'bullish', TradeSignalOutcomeState::StopHit, TradeSignalOutcomeLabel::Loss);
        $this->makeOutcome('breakout_confirmation', 'SPY', '4h', 'bullish', TradeSignalOutcomeState::TargetHit, TradeSignalOutcomeLabel::Win);
        $this->makeOutcome('breakout_confirmation', 'QQQ', '4h', 'bearish', TradeSignalOutcomeState::TargetHit, TradeSignalOutcomeLabel::Win);
        $this->makeOutcome('breakout_confirmation', 'TSLA', '1d', 'bearish', TradeSignalOutcomeState::EntryNotReached, TradeSignalOutcomeLabel::Neutral);

        $rows = app(TradeSignalStrategyComparison::class)->leaderboard()->keyBy('strategy_key');

        $this->assertSame(2, $rows['trend_continuation']['signal_count']);
        $this->assertSame(50.0, $rows['trend_continuation']['win_rate']);
        $this->assertSame('Very low sample size; treat results as provisional.', $rows['trend_continuation']['sample_size_note']);
        $this->assertSame(3, $rows['breakout_confirmation']['signal_count']);
        $this->assertSame(100.0, $rows['breakout_confirmation']['win_rate']);
        $this->assertGreaterThan($rows['trend_continuation']['comparison_score'], $rows['breakout_confirmation']['comparison_score']);
    }

    public function test_it_builds_strategy_detail_with_symbol_timeframe_and_direction_breakdowns(): void
    {
        $this->makeOutcome('trend_continuation', 'NVDA', '4h', 'bullish', TradeSignalOutcomeState::TargetHit, TradeSignalOutcomeLabel::Win);
        $this->makeOutcome('trend_continuation', 'NVDA', '1d', 'bullish', TradeSignalOutcomeState::StopHit, TradeSignalOutcomeLabel::Loss);
        $this->makeOutcome('trend_continuation', 'AAPL', '4h', 'bearish', TradeSignalOutcomeState::EntryNotReached, TradeSignalOutcomeLabel::Neutral);

        $detail = app(TradeSignalStrategyComparison::class)->detail('trend_continuation');

        $this->assertSame('trend_continuation', $detail['strategy_key']);
        $this->assertSame(3, $detail['signal_count']);
        $this->assertCount(2, $detail['symbol_breakdown']);
        $this->assertCount(2, $detail['timeframe_breakdown']);
        $this->assertCount(2, $detail['direction_breakdown']);
        $this->assertSame(2, $detail['bullish_signal_count']);
        $this->assertSame(1, $detail['bearish_signal_count']);
    }

    private function makeOutcome(
        string $strategyKey,
        string $symbol,
        string $timeframe,
        string $direction,
        TradeSignalOutcomeState $state,
        TradeSignalOutcomeLabel $label,
    ): TradeSignalOutcome {
        $symbolModel = Symbol::query()->firstOrCreate(
            [
                'market' => 'us_equities',
                'symbol' => $symbol,
            ],
            [
                'asset_type' => 'stock',
                'name' => $symbol.' Test',
                'provider' => 'manual',
                'provider_symbol' => $symbol,
            ],
        );

        $signal = TradeSignal::query()->create([
            'symbol_id' => $symbolModel->id,
            'strategy_key' => $strategyKey,
            'timeframe' => $timeframe,
            'direction' => $direction,
            'execution_hint' => $direction === 'bullish' ? 'call' : 'put',
            'signal_category' => $strategyKey,
            'entry_price' => 100,
            'stop_loss' => 95,
            'target_price' => 110,
            'thesis' => 'Strategy comparison test signal.',
            'signal_generated_at' => '2026-03-31T12:00:00+00:00',
        ]);

        return TradeSignalOutcome::query()->create([
            'trade_signal_id' => $signal->id,
            'evaluation_state' => $state,
            'outcome_label' => $label,
            'entry_reached' => $state !== TradeSignalOutcomeState::EntryNotReached,
            'target_hit' => $state === TradeSignalOutcomeState::TargetHit,
            'stop_hit' => $state === TradeSignalOutcomeState::StopHit,
            'evaluation_started_at' => '2026-03-31T12:00:00+00:00',
            'evaluation_completed_at' => '2026-03-31T16:00:00+00:00',
            'evaluation_assumption_key' => 'first_touch_v1',
        ]);
    }
}
