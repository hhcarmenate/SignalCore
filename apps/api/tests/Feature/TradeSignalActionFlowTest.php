<?php

namespace Tests\Feature;

use App\Enums\TradeSignalActionType;
use App\Enums\TradeSignalStatus;
use App\Models\Symbol;
use App\Models\TradeSignal;
use App\Support\Signals\TradeSignalActionManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class TradeSignalActionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_applies_manual_review_and_accept_actions(): void
    {
        $signal = $this->makeSignal();
        $manager = app(TradeSignalActionManager::class);

        $queued = $manager->apply($signal, TradeSignalActionType::QueueForReview, 'Queued manually.');
        $this->assertSame(TradeSignalStatus::PendingReview->value, $queued->status);
        $this->assertSame(TradeSignalActionType::QueueForReview->value, $queued->last_action);
        $this->assertNotNull($queued->queued_for_review_at);

        $accepted = $manager->apply($queued, TradeSignalActionType::Accept, 'Accepted manually.');
        $this->assertSame(TradeSignalStatus::Accepted->value, $accepted->status);
        $this->assertSame(TradeSignalActionType::Accept->value, $accepted->last_action);
        $this->assertNotNull($accepted->reviewed_at);
    }

    public function test_it_applies_system_expire_and_manual_actioned_updates(): void
    {
        $manager = app(TradeSignalActionManager::class);

        $expired = $manager->apply($this->makeSignal(), TradeSignalActionType::Expire, 'Expired automatically.');
        $this->assertSame(TradeSignalStatus::Expired->value, $expired->status);
        $this->assertSame(TradeSignalActionType::Expire->value, $expired->last_action);
        $this->assertNotNull($expired->invalidated_at);

        $accepted = $manager->apply($this->makeSignal(), TradeSignalActionType::Accept, 'Approved.');
        $actioned = $manager->apply($accepted, TradeSignalActionType::MarkActioned, 'Trade taken.');
        $this->assertSame(TradeSignalStatus::Actioned->value, $actioned->status);
        $this->assertSame(TradeSignalActionType::MarkActioned->value, $actioned->last_action);
        $this->assertNotNull($actioned->actioned_at);
    }

    public function test_it_rejects_invalid_backwards_action_flow(): void
    {
        $manager = app(TradeSignalActionManager::class);
        $accepted = $manager->apply($this->makeSignal(), TradeSignalActionType::Accept, 'Accepted first.');

        $this->expectException(InvalidArgumentException::class);

        $manager->apply($accepted, TradeSignalActionType::QueueForReview, 'Trying to move backwards.');
    }

    private function makeSignal(): TradeSignal
    {
        $symbol = Symbol::query()->create([
            'asset_type' => 'stock',
            'symbol' => fake()->unique()->lexify('ACT??'),
            'name' => 'Action Test Symbol',
            'market' => 'us_equities',
            'provider' => 'manual',
            'provider_symbol' => fake()->unique()->lexify('ACT??'),
        ]);

        return TradeSignal::query()->create([
            'symbol_id' => $symbol->id,
            'strategy_key' => 'trend_continuation',
            'timeframe' => '4h',
            'direction' => 'bullish',
            'signal_category' => 'trend_continuation',
            'thesis' => 'Action flow test signal.',
        ]);
    }
}
