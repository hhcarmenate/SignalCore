<?php

namespace Tests\Feature;

use App\Enums\TradeSignalOutcomeLabel;
use App\Enums\TradeSignalOutcomeState;
use App\Models\Candle;
use App\Models\Symbol;
use App\Models\TradeSignal;
use App\Support\Signals\TradeSignalEvaluationWorkflow;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TradeSignalEvaluationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stays_pending_when_market_data_is_unavailable(): void
    {
        $signal = $this->makeSignal();

        $this->assertFalse(app(TradeSignalEvaluationWorkflow::class)->isReady($signal, CarbonImmutable::parse('2026-03-31T18:00:00Z')));

        $outcome = app(TradeSignalEvaluationWorkflow::class)->evaluate($signal, CarbonImmutable::parse('2026-03-31T18:00:00Z'));

        $this->assertSame(TradeSignalOutcomeState::Pending, $outcome->evaluation_state);
        $this->assertSame(TradeSignalOutcomeLabel::Unresolved, $outcome->outcome_label);
        $this->assertStringContainsString('market_data_unavailable', (string) $outcome->notes);
    }

    public function test_it_marks_entry_not_reached_when_window_ends_without_entry(): void
    {
        $signal = $this->makeSignal(expiresAt: '2026-04-01T12:00:00Z');
        $this->makeCandle($signal, '2026-03-31T16:00:00Z', 98, 99, 97, 98.5);
        $this->makeCandle($signal, '2026-04-01T08:00:00Z', 99, 99.5, 97.5, 98.1);

        $outcome = app(TradeSignalEvaluationWorkflow::class)->evaluate($signal, CarbonImmutable::parse('2026-04-01T13:00:00Z'));

        $this->assertSame(TradeSignalOutcomeState::EntryNotReached, $outcome->evaluation_state);
        $this->assertSame(TradeSignalOutcomeLabel::Neutral, $outcome->outcome_label);
        $this->assertTrue($outcome->expired_without_entry);
    }

    public function test_it_resolves_target_hit_after_entry(): void
    {
        $signal = $this->makeSignal();
        $this->makeCandle($signal, '2026-03-31T16:00:00Z', 99, 101, 98, 100.5);
        $this->makeCandle($signal, '2026-03-31T20:00:00Z', 100.5, 106, 100, 105.5);

        $outcome = app(TradeSignalEvaluationWorkflow::class)->evaluate($signal, CarbonImmutable::parse('2026-04-01T00:00:00Z'));

        $this->assertSame(TradeSignalOutcomeState::TargetHit, $outcome->evaluation_state);
        $this->assertSame(TradeSignalOutcomeLabel::Win, $outcome->outcome_label);
        $this->assertTrue($outcome->entry_reached);
        $this->assertTrue($outcome->target_hit);
        $this->assertFalse($outcome->stop_hit);
    }

    public function test_it_resolves_stop_hit_after_entry(): void
    {
        $signal = $this->makeSignal();
        $this->makeCandle($signal, '2026-03-31T16:00:00Z', 99, 101, 98, 100.5);
        $this->makeCandle($signal, '2026-03-31T20:00:00Z', 100, 100.5, 94, 95);

        $outcome = app(TradeSignalEvaluationWorkflow::class)->evaluate($signal, CarbonImmutable::parse('2026-04-01T00:00:00Z'));

        $this->assertSame(TradeSignalOutcomeState::StopHit, $outcome->evaluation_state);
        $this->assertSame(TradeSignalOutcomeLabel::Loss, $outcome->outcome_label);
        $this->assertTrue($outcome->stop_hit);
    }

    public function test_it_marks_ambiguous_same_bar_when_stop_and_target_are_both_reachable(): void
    {
        $signal = $this->makeSignal();
        $this->makeCandle($signal, '2026-03-31T16:00:00Z', 99, 111, 94, 103);

        $outcome = app(TradeSignalEvaluationWorkflow::class)->evaluate($signal, CarbonImmutable::parse('2026-04-01T00:00:00Z'));

        $this->assertSame(TradeSignalOutcomeState::AmbiguousSameBar, $outcome->evaluation_state);
        $this->assertSame(TradeSignalOutcomeLabel::Neutral, $outcome->outcome_label);
        $this->assertSame('ambiguous_same_bar', $outcome->ambiguity_reason);
    }

    public function test_it_supports_explicit_reevaluation(): void
    {
        $signal = $this->makeSignal();
        $this->makeCandle($signal, '2026-03-31T16:00:00Z', 99, 101, 98, 100.5);
        $this->makeCandle($signal, '2026-03-31T20:00:00Z', 100, 100.5, 94, 95);

        $workflow = app(TradeSignalEvaluationWorkflow::class);
        $workflow->evaluate($signal, CarbonImmutable::parse('2026-04-01T00:00:00Z'));

        Candle::query()->where('symbol_id', $signal->symbol_id)->where('bar_time', '2026-03-31T20:00:00+00:00')->update([
            'high' => 106,
            'low' => 100,
            'close' => 105,
        ]);

        $outcome = $workflow->reevaluate($signal->fresh('outcome'), 'corrected candle data', CarbonImmutable::parse('2026-04-01T00:00:00Z'));

        $this->assertSame(TradeSignalOutcomeState::TargetHit, $outcome->evaluation_state);
        $this->assertStringContainsString('Re-evaluated: corrected candle data', (string) $outcome->notes);
    }

    private function makeSignal(string $expiresAt = '2026-04-02T12:00:00Z'): TradeSignal
    {
        $symbol = Symbol::query()->create([
            'asset_type' => 'stock',
            'symbol' => fake()->unique()->lexify('EVL??'),
            'name' => 'Evaluation Test Symbol',
            'market' => 'us_equities',
            'provider' => 'manual',
            'provider_symbol' => fake()->unique()->lexify('EVL??'),
        ]);

        return TradeSignal::query()->create([
            'symbol_id' => $symbol->id,
            'strategy_key' => 'trend_continuation',
            'timeframe' => '4h',
            'direction' => 'bullish',
            'execution_hint' => 'call',
            'signal_category' => 'trend_continuation',
            'entry_price' => 100,
            'stop_loss' => 95,
            'target_price' => 105,
            'thesis' => 'Evaluation workflow test signal.',
            'signal_generated_at' => '2026-03-31T12:00:00Z',
            'expires_at' => $expiresAt,
        ]);
    }

    private function makeCandle(TradeSignal $signal, string $barTime, float $open, float $high, float $low, float $close): Candle
    {
        return Candle::query()->create([
            'symbol_id' => $signal->symbol_id,
            'timeframe' => $signal->timeframe,
            'bar_time' => $barTime,
            'open' => $open,
            'high' => $high,
            'low' => $low,
            'close' => $close,
            'volume' => 100000,
            'provider' => 'manual',
            'is_final' => true,
        ]);
    }
}
