<?php

namespace Tests\Feature;

use App\Enums\TradeSignalOutcomeLabel;
use App\Enums\TradeSignalOutcomeState;
use App\Models\Symbol;
use App\Models\TradeSignal;
use App\Models\TradeSignalOutcome;
use App\Support\Signals\TradeSignalPerformanceMetrics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TradeSignalPerformanceMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calculates_required_summary_metrics_with_correct_denominators(): void
    {
        $nvda = $this->makeSignalWithOutcome('NVDA', '4h', TradeSignalOutcomeState::TargetHit, TradeSignalOutcomeLabel::Win, true, true, false);
        $aapl = $this->makeSignalWithOutcome('AAPL', '4h', TradeSignalOutcomeState::StopHit, TradeSignalOutcomeLabel::Loss, true, false, true);
        $spy = $this->makeSignalWithOutcome('SPY', '1d', TradeSignalOutcomeState::EntryNotReached, TradeSignalOutcomeLabel::Neutral, false, false, false);
        $qqq = $this->makeSignalWithOutcome('QQQ', '1d', TradeSignalOutcomeState::Pending, TradeSignalOutcomeLabel::Unresolved, false, false, false);
        $tsla = $this->makeSignalWithOutcome('TSLA', '4h', TradeSignalOutcomeState::ExpiredAfterEntry, TradeSignalOutcomeLabel::Neutral, true, false, false);

        $summary = app(TradeSignalPerformanceMetrics::class)->summarize();

        $this->assertSame(5, $summary['signal_count']);
        $this->assertSame(2, $summary['resolved_count']);
        $this->assertSame(3, $summary['entered_count']);
        $this->assertSame(1, $summary['win_count']);
        $this->assertSame(1, $summary['loss_count']);
        $this->assertSame(2, $summary['neutral_count']);
        $this->assertSame(1, $summary['unresolved_count']);
        $this->assertSame(1, $summary['target_hit_count']);
        $this->assertSame(1, $summary['stop_hit_count']);
        $this->assertSame(50.0, $summary['win_rate']);
        $this->assertSame(33.33, $summary['target_hit_rate']);
        $this->assertSame(33.33, $summary['stop_hit_rate']);
        $this->assertSame(0.0, $summary['average_r_multiple']);
    }

    public function test_it_groups_metrics_by_symbol(): void
    {
        $this->makeSignalWithOutcome('NVDA', '4h', TradeSignalOutcomeState::TargetHit, TradeSignalOutcomeLabel::Win, true, true, false, '2026-03-31T14:00:00+00:00');
        $this->makeSignalWithOutcome('NVDA', '1d', TradeSignalOutcomeState::StopHit, TradeSignalOutcomeLabel::Loss, true, false, true, '2026-03-31T16:00:00+00:00');
        $this->makeSignalWithOutcome('AAPL', '4h', TradeSignalOutcomeState::EntryNotReached, TradeSignalOutcomeLabel::Neutral, false, false, false, '2026-03-31T18:00:00+00:00');

        $rows = app(TradeSignalPerformanceMetrics::class)->bySymbol()->keyBy('symbol');

        $this->assertSame(2, $rows['NVDA']['signal_count']);
        $this->assertSame(2, $rows['NVDA']['resolved_count']);
        $this->assertSame(50.0, $rows['NVDA']['win_rate']);
        $this->assertSame('2026-03-31T16:00:00.000000Z', $rows['NVDA']['last_signal_at']);
        $this->assertSame(1, $rows['AAPL']['signal_count']);
        $this->assertNull($rows['AAPL']['win_rate']);
    }

    public function test_it_groups_metrics_by_timeframe(): void
    {
        $this->makeSignalWithOutcome('NVDA', '4h', TradeSignalOutcomeState::TargetHit, TradeSignalOutcomeLabel::Win, true, true, false);
        $this->makeSignalWithOutcome('AAPL', '4h', TradeSignalOutcomeState::ExpiredAfterEntry, TradeSignalOutcomeLabel::Neutral, true, false, false);
        $this->makeSignalWithOutcome('SPY', '1d', TradeSignalOutcomeState::StopHit, TradeSignalOutcomeLabel::Loss, true, false, true);

        $rows = app(TradeSignalPerformanceMetrics::class)->byTimeframe()->keyBy('timeframe');

        $this->assertSame(2, $rows['4h']['signal_count']);
        $this->assertSame(1, $rows['4h']['resolved_count']);
        $this->assertSame(100.0, $rows['4h']['win_rate']);
        $this->assertSame(1, $rows['1d']['signal_count']);
        $this->assertSame(0.0, $rows['1d']['win_rate']);
        $this->assertSame(-1.0, $rows['1d']['average_r_multiple']);
    }

    private function makeSignalWithOutcome(
        string $symbol,
        string $timeframe,
        TradeSignalOutcomeState $state,
        TradeSignalOutcomeLabel $label,
        bool $entryReached,
        bool $targetHit,
        bool $stopHit,
        string $generatedAt = '2026-03-31T12:00:00+00:00',
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
            'strategy_key' => 'trend_continuation',
            'timeframe' => $timeframe,
            'direction' => 'bullish',
            'execution_hint' => 'call',
            'signal_category' => 'trend_continuation',
            'entry_price' => 100,
            'stop_loss' => 95,
            'target_price' => 110,
            'thesis' => 'Performance metrics test signal.',
            'signal_generated_at' => $generatedAt,
        ]);

        return TradeSignalOutcome::query()->create([
            'trade_signal_id' => $signal->id,
            'evaluation_state' => $state,
            'outcome_label' => $label,
            'entry_reached' => $entryReached,
            'target_hit' => $targetHit,
            'stop_hit' => $stopHit,
            'evaluation_started_at' => '2026-03-31T12:00:00+00:00',
            'evaluation_completed_at' => '2026-03-31T18:00:00+00:00',
            'evaluation_assumption_key' => 'first_touch_v1',
        ]);
    }
}
