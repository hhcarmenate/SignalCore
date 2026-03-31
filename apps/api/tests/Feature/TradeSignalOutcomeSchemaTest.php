<?php

namespace Tests\Feature;

use App\Enums\TradeSignalOutcomeLabel;
use App\Enums\TradeSignalOutcomeState;
use App\Models\Symbol;
use App\Models\TradeSignal;
use App\Models\TradeSignalOutcome;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TradeSignalOutcomeSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_persists_trade_signal_outcomes_with_required_tracking_fields(): void
    {
        $signal = $this->makeSignal();

        $outcome = TradeSignalOutcome::query()->create([
            'trade_signal_id' => $signal->id,
            'evaluation_state' => TradeSignalOutcomeState::TargetHit,
            'outcome_label' => TradeSignalOutcomeLabel::Win,
            'entry_reached' => true,
            'entry_reached_at' => '2026-03-31T14:05:00+00:00',
            'target_hit' => true,
            'target_hit_at' => '2026-03-31T18:15:00+00:00',
            'stop_hit' => false,
            'expired_without_entry' => false,
            'expired_after_entry' => false,
            'evaluation_started_at' => '2026-03-31T14:00:00+00:00',
            'evaluation_completed_at' => '2026-03-31T18:15:00+00:00',
            'evaluation_assumption_key' => 'first_touch_v1',
            'notes' => 'Resolved cleanly after entry activation.',
            'max_favorable_excursion' => 4.25,
            'max_adverse_excursion' => 0.85,
            'price_after_1d' => 104.75,
        ]);

        $this->assertSame($signal->id, $outcome->trade_signal_id);
        $this->assertTrue($outcome->entry_reached);
        $this->assertTrue($outcome->target_hit);
        $this->assertFalse($outcome->stop_hit);
        $this->assertSame(TradeSignalOutcomeState::TargetHit, $outcome->evaluation_state);
        $this->assertSame(TradeSignalOutcomeLabel::Win, $outcome->outcome_label);
        $this->assertSame('4.25000000', $outcome->max_favorable_excursion);
        $this->assertSame('0.85000000', $outcome->max_adverse_excursion);
        $this->assertSame('104.75000000', $outcome->price_after_1d);
    }

    public function test_it_exposes_a_one_to_one_outcome_relationship_from_trade_signal(): void
    {
        $signal = new TradeSignal;
        $relation = $signal->outcome();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $relation);
        $this->assertSame('trade_signal_id', $relation->getForeignKeyName());
    }

    private function makeSignal(): TradeSignal
    {
        $symbol = Symbol::query()->create([
            'asset_type' => 'stock',
            'symbol' => fake()->unique()->lexify('OUT??'),
            'name' => 'Outcome Test Symbol',
            'market' => 'us_equities',
            'provider' => 'manual',
            'provider_symbol' => fake()->unique()->lexify('OUT??'),
        ]);

        return TradeSignal::query()->create([
            'symbol_id' => $symbol->id,
            'strategy_key' => 'trend_continuation',
            'timeframe' => '4h',
            'direction' => 'bullish',
            'execution_hint' => 'call',
            'signal_category' => 'trend_continuation',
            'entry_price' => 100,
            'stop_loss' => 96,
            'target_price' => 105,
            'thesis' => 'Outcome tracking seed signal.',
            'signal_generated_at' => '2026-03-31T14:00:00+00:00',
            'expires_at' => '2026-04-02T14:00:00+00:00',
        ]);
    }
}
