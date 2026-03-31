<?php

namespace Tests\Feature;

use App\Enums\TradeSignalOutcomeLabel;
use App\Enums\TradeSignalOutcomeState;
use App\Models\Symbol;
use App\Models\TradeSignal;
use App\Models\TradeSignalOutcome;
use App\Support\Signals\TradeSignalTuningFeedbackLoop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TradeSignalTuningFeedbackLoopTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_flags_weak_strategies_for_threshold_tuning(): void
    {
        $this->makeOutcome('weak_strategy', 'AAA', '4h', 'bullish', TradeSignalOutcomeState::StopHit, TradeSignalOutcomeLabel::Loss);
        $this->makeOutcome('weak_strategy', 'BBB', '4h', 'bullish', TradeSignalOutcomeState::StopHit, TradeSignalOutcomeLabel::Loss);
        $this->makeOutcome('weak_strategy', 'CCC', '1d', 'bearish', TradeSignalOutcomeState::StopHit, TradeSignalOutcomeLabel::Loss);
        $this->makeOutcome('weak_strategy', 'DDD', '1d', 'bearish', TradeSignalOutcomeState::TargetHit, TradeSignalOutcomeLabel::Win);
        $this->makeOutcome('weak_strategy', 'EEE', '4h', 'bullish', TradeSignalOutcomeState::ExpiredAfterEntry, TradeSignalOutcomeLabel::Neutral);

        $recommendation = app(TradeSignalTuningFeedbackLoop::class)->strategyRecommendation('weak_strategy');

        $this->assertSame('tune_thresholds', $recommendation['decision']);
        $this->assertSame('weekly', $recommendation['review_cadence']);
        $this->assertNotEmpty($recommendation['candidate_tuning_inputs']);
        $this->assertStringContainsString('Win rate is materially weak', implode(' ', $recommendation['reasons']));
    }

    public function test_it_recommends_observe_longer_for_small_samples(): void
    {
        $this->makeOutcome('small_sample', 'AAA', '4h', 'bullish', TradeSignalOutcomeState::TargetHit, TradeSignalOutcomeLabel::Win);
        $this->makeOutcome('small_sample', 'BBB', '4h', 'bullish', TradeSignalOutcomeState::StopHit, TradeSignalOutcomeLabel::Loss);

        $recommendation = app(TradeSignalTuningFeedbackLoop::class)->strategyRecommendation('small_sample');

        $this->assertSame('observe_longer', $recommendation['decision']);
        $this->assertSame('low', $recommendation['confidence']);
    }

    public function test_it_recommends_promoting_strong_strategies(): void
    {
        foreach (range(1, 6) as $index) {
            $this->makeOutcome('strong_strategy', 'S'.$index, '4h', 'bullish', TradeSignalOutcomeState::TargetHit, TradeSignalOutcomeLabel::Win);
        }

        $recommendation = app(TradeSignalTuningFeedbackLoop::class)->strategyRecommendation('strong_strategy');

        $this->assertSame('promote_strategy', $recommendation['decision']);
        $this->assertSame('monthly', $recommendation['review_cadence']);
    }

    public function test_it_builds_review_candidates_for_multiple_strategies(): void
    {
        foreach (range(1, 6) as $index) {
            $this->makeOutcome('strong_strategy', 'STR'.$index, '4h', 'bullish', TradeSignalOutcomeState::TargetHit, TradeSignalOutcomeLabel::Win);
        }

        foreach (range(1, 5) as $index) {
            $state = $index < 4 ? TradeSignalOutcomeState::StopHit : TradeSignalOutcomeState::TargetHit;
            $label = $index < 4 ? TradeSignalOutcomeLabel::Loss : TradeSignalOutcomeLabel::Win;
            $this->makeOutcome('weak_strategy', 'WEK'.$index, '1d', 'bearish', $state, $label);
        }

        $rows = app(TradeSignalTuningFeedbackLoop::class)->reviewCandidates()->keyBy('strategy_key');

        $this->assertSame('promote_strategy', $rows['strong_strategy']['decision']);
        $this->assertSame('tune_thresholds', $rows['weak_strategy']['decision']);
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
            'thesis' => 'Feedback loop test signal.',
            'signal_generated_at' => '2026-03-31T12:00:00+00:00',
        ]);

        return TradeSignalOutcome::query()->create([
            'trade_signal_id' => $signal->id,
            'evaluation_state' => $state,
            'outcome_label' => $label,
            'entry_reached' => $state !== TradeSignalOutcomeState::EntryNotReached,
            'target_hit' => $state === TradeSignalOutcomeState::TargetHit,
            'stop_hit' => $state === TradeSignalOutcomeState::StopHit,
            'expired_after_entry' => $state === TradeSignalOutcomeState::ExpiredAfterEntry,
            'evaluation_started_at' => '2026-03-31T12:00:00+00:00',
            'evaluation_completed_at' => '2026-03-31T16:00:00+00:00',
            'evaluation_assumption_key' => 'first_touch_v1',
        ]);
    }
}
