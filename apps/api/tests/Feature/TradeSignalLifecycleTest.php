<?php

namespace Tests\Feature;

use App\Enums\TradeSignalStatus;
use App\Models\Symbol;
use App\Models\TradeSignal;
use App\Support\Signals\TradeSignalLifecycleManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class TradeSignalLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_uses_the_expected_default_signal_status(): void
    {
        $symbol = Symbol::query()->create([
            'asset_type' => 'stock',
            'symbol' => 'SPY',
            'name' => 'SPDR S&P 500 ETF Trust',
            'market' => 'us_equities',
            'provider' => 'manual',
            'provider_symbol' => 'SPY',
        ]);

        $signal = TradeSignal::query()->create([
            'symbol_id' => $symbol->id,
            'strategy_key' => 'trend_continuation',
            'timeframe' => '4h',
            'direction' => 'bullish',
            'signal_category' => 'trend_continuation',
            'thesis' => 'Default status test.',
        ]);

        $this->assertSame(TradeSignalStatus::New->value, $signal->status);
    }

    public function test_it_transitions_from_new_to_pending_review_and_then_to_accepted(): void
    {
        $signal = $this->makeSignal();
        $manager = new TradeSignalLifecycleManager;

        $signal = $manager->transition($signal, TradeSignalStatus::PendingReview, 'Queued for analyst review.');
        $this->assertSame(TradeSignalStatus::PendingReview->value, $signal->status);
        $this->assertSame('Queued for analyst review.', $signal->status_reason);
        $this->assertNull($signal->reviewed_at);

        $signal = $manager->transition($signal, TradeSignalStatus::Accepted, 'Confirmed after review.');
        $this->assertSame(TradeSignalStatus::Accepted->value, $signal->status);
        $this->assertSame('Confirmed after review.', $signal->status_reason);
        $this->assertNotNull($signal->reviewed_at);
    }

    public function test_it_marks_invalidated_or_actioned_timestamps_for_terminal_states(): void
    {
        $manager = new TradeSignalLifecycleManager;

        $expired = $manager->transition($this->makeSignal(), TradeSignalStatus::Expired, 'Signal timed out.');
        $this->assertNotNull($expired->invalidated_at);

        $accepted = $manager->transition($this->makeSignal(), TradeSignalStatus::Accepted, 'Approved.');
        $actioned = $manager->transition($accepted, TradeSignalStatus::Actioned, 'Trade taken.');
        $this->assertNotNull($actioned->actioned_at);
    }

    public function test_it_rejects_invalid_status_transitions(): void
    {
        $signal = $this->makeSignal();
        $manager = new TradeSignalLifecycleManager;

        $accepted = $manager->transition($signal, TradeSignalStatus::Accepted, 'Approved immediately.');

        $this->expectException(InvalidArgumentException::class);

        $manager->transition($accepted, TradeSignalStatus::PendingReview, 'Trying to move backwards.');
    }

    private function makeSignal(): TradeSignal
    {
        $symbol = Symbol::query()->create([
            'asset_type' => 'stock',
            'symbol' => fake()->unique()->lexify('SYM??'),
            'name' => 'Signal Test Symbol',
            'market' => 'us_equities',
            'provider' => 'manual',
            'provider_symbol' => fake()->unique()->lexify('SYM??'),
        ]);

        return TradeSignal::query()->create([
            'symbol_id' => $symbol->id,
            'strategy_key' => 'trend_continuation',
            'timeframe' => '4h',
            'direction' => 'bullish',
            'signal_category' => 'trend_continuation',
            'thesis' => 'Lifecycle test signal.',
        ]);
    }
}
