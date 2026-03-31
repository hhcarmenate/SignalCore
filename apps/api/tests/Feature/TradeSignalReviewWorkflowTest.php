<?php

namespace Tests\Feature;

use App\Enums\TradeSignalStatus;
use App\Models\Symbol;
use App\Models\TradeSignal;
use App\Support\Signals\TradeSignalReviewWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TradeSignalReviewWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_queues_a_signal_for_review(): void
    {
        $signal = $this->makeSignal();

        $queued = app(TradeSignalReviewWorkflow::class)->queue($signal, 'Needs analyst review.');

        $this->assertSame(TradeSignalStatus::PendingReview->value, $queued->status);
        $this->assertNotNull($queued->queued_for_review_at);
        $this->assertSame('Needs analyst review.', $queued->review_summary);
    }

    public function test_it_appends_review_notes(): void
    {
        $signal = app(TradeSignalReviewWorkflow::class)->queue($this->makeSignal(), 'Queued.');

        $updated = app(TradeSignalReviewWorkflow::class)->addNote($signal, 'Trend structure looks valid.');

        $this->assertCount(1, $updated->review_notes);
        $this->assertSame('Trend structure looks valid.', $updated->review_notes[0]['note']);
    }

    public function test_it_accepts_and_rejects_signals_through_review_workflow(): void
    {
        $queued = app(TradeSignalReviewWorkflow::class)->queue($this->makeSignal(), 'Queued.');
        $accepted = app(TradeSignalReviewWorkflow::class)->accept($queued, 'Approved for action.');

        $this->assertSame(TradeSignalStatus::Accepted->value, $accepted->status);
        $this->assertSame('Approved for action.', $accepted->review_summary);
        $this->assertNotNull($accepted->reviewed_at);

        $queuedAgain = app(TradeSignalReviewWorkflow::class)->queue($this->makeSignal(), 'Queued again.');
        $rejected = app(TradeSignalReviewWorkflow::class)->reject($queuedAgain, 'Rejected as low quality.');

        $this->assertSame(TradeSignalStatus::Rejected->value, $rejected->status);
        $this->assertSame('Rejected as low quality.', $rejected->review_summary);
        $this->assertNotNull($rejected->reviewed_at);
    }

    private function makeSignal(): TradeSignal
    {
        $symbol = Symbol::query()->create([
            'asset_type' => 'stock',
            'symbol' => fake()->unique()->lexify('REV??'),
            'name' => 'Review Test Symbol',
            'market' => 'us_equities',
            'provider' => 'manual',
            'provider_symbol' => fake()->unique()->lexify('REV??'),
        ]);

        return TradeSignal::query()->create([
            'symbol_id' => $symbol->id,
            'strategy_key' => 'breakout_confirmation',
            'timeframe' => '4h',
            'direction' => 'bullish',
            'signal_category' => 'breakout_confirmation',
            'thesis' => 'Review workflow test signal.',
        ]);
    }
}
