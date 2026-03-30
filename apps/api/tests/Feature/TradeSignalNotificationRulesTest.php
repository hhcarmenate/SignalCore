<?php

namespace Tests\Feature;

use App\Models\Symbol;
use App\Models\TradeSignal;
use App\Support\Signals\TradeSignalNotificationRules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TradeSignalNotificationRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_classifies_high_priority_notifications(): void
    {
        $signal = $this->makeSignal(score: 90, confidence: 82, timeframe: '4h');

        $updated = app(TradeSignalNotificationRules::class)->apply($signal);

        $this->assertSame('high', $updated->notification_priority);
        $this->assertTrue($updated->should_notify);
    }

    public function test_it_filters_out_low_quality_or_unsupported_timeframes(): void
    {
        $lowQuality = app(TradeSignalNotificationRules::class)->apply(
            $this->makeSignal(score: 60, confidence: 55, timeframe: '4h')
        );
        $unsupportedTimeframe = app(TradeSignalNotificationRules::class)->apply(
            $this->makeSignal(score: 88, confidence: 80, timeframe: '15m')
        );

        $this->assertSame('low', $lowQuality->notification_priority);
        $this->assertFalse($lowQuality->should_notify);
        $this->assertFalse($unsupportedTimeframe->should_notify);
    }

    public function test_it_ignores_non_new_or_non_accepted_signals_for_notifications(): void
    {
        $signal = $this->makeSignal(score: 88, confidence: 80, timeframe: '1d', status: 'rejected');

        $updated = app(TradeSignalNotificationRules::class)->apply($signal);

        $this->assertSame('high', $updated->notification_priority);
        $this->assertFalse($updated->should_notify);
    }

    private function makeSignal(int $score, int $confidence, string $timeframe, string $status = 'new'): TradeSignal
    {
        $symbol = Symbol::query()->create([
            'asset_type' => 'stock',
            'symbol' => fake()->unique()->lexify('NTF??'),
            'name' => 'Notification Test Symbol',
            'market' => 'us_equities',
            'provider' => 'manual',
            'provider_symbol' => fake()->unique()->lexify('NTF??'),
        ]);

        return TradeSignal::query()->create([
            'symbol_id' => $symbol->id,
            'strategy_key' => 'trend_continuation',
            'timeframe' => $timeframe,
            'direction' => 'bullish',
            'signal_category' => 'trend_continuation',
            'status' => $status,
            'score' => $score,
            'confidence' => $confidence,
            'thesis' => 'Notification workflow test signal.',
        ]);
    }
}
