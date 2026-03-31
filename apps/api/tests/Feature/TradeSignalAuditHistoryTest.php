<?php

namespace Tests\Feature;

use App\Models\Symbol;
use App\Models\TradeSignal;
use App\Support\Signals\TradeSignalAuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TradeSignalAuditHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_logs_auditable_signal_events(): void
    {
        $signal = $this->makeSignal();

        $audit = app(TradeSignalAuditLogger::class)->log(
            signal: $signal,
            eventType: 'status_changed',
            statusBefore: 'new',
            statusAfter: 'accepted',
            actionType: 'accept',
            reason: 'Approved after review.',
            notes: [['note' => 'Strong structure']],
            metadata: ['source' => 'manual_review'],
        );

        $this->assertSame($signal->id, $audit->trade_signal_id);
        $this->assertSame('status_changed', $audit->event_type);
        $this->assertSame('new', $audit->status_before);
        $this->assertSame('accepted', $audit->status_after);
        $this->assertSame('accept', $audit->action_type);
        $this->assertSame('manual_review', $audit->metadata['source']);
    }

    public function test_it_exposes_signal_audits_in_latest_first_order(): void
    {
        $signal = $this->makeSignal();
        $logger = app(TradeSignalAuditLogger::class);

        $logger->log($signal, 'queued', 'new', 'pending_review', 'queue_for_review', 'Queued.');
        sleep(1);
        $logger->log($signal, 'accepted', 'pending_review', 'accepted', 'accept', 'Accepted.');

        $audits = $signal->fresh()->audits;

        $this->assertCount(2, $audits);
        $this->assertSame('accepted', $audits->first()->event_type);
        $this->assertSame('queued', $audits->last()->event_type);
    }

    private function makeSignal(): TradeSignal
    {
        $symbol = Symbol::query()->create([
            'asset_type' => 'stock',
            'symbol' => fake()->unique()->lexify('AUD??'),
            'name' => 'Audit Test Symbol',
            'market' => 'us_equities',
            'provider' => 'manual',
            'provider_symbol' => fake()->unique()->lexify('AUD??'),
        ]);

        return TradeSignal::query()->create([
            'symbol_id' => $symbol->id,
            'strategy_key' => 'trend_continuation',
            'timeframe' => '4h',
            'direction' => 'bullish',
            'signal_category' => 'trend_continuation',
            'thesis' => 'Audit history test signal.',
        ]);
    }
}
