<?php

namespace App\Support\Signals;

use App\Models\TradeSignal;
use Illuminate\Support\Str;

class TradeSignalDeduplicator
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function fingerprint(array $attributes): string
    {
        return sha1(implode('|', [
            (string) ($attributes['symbol_id'] ?? ''),
            (string) ($attributes['strategy_key'] ?? ''),
            (string) ($attributes['timeframe'] ?? ''),
            (string) ($attributes['direction'] ?? ''),
            (string) ($attributes['bar_time'] ?? ''),
        ]));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function setupKey(array $attributes): string
    {
        return Str::lower(implode(':', [
            (string) ($attributes['symbol_id'] ?? ''),
            (string) ($attributes['strategy_key'] ?? ''),
            (string) ($attributes['timeframe'] ?? ''),
            (string) ($attributes['direction'] ?? ''),
        ]));
    }

    public function sameBarDuplicate(TradeSignal $signal): ?TradeSignal
    {
        return TradeSignal::query()
            ->where('fingerprint', $signal->fingerprint)
            ->whereKeyNot($signal->id)
            ->first();
    }

    public function latestSameSetup(TradeSignal $signal): ?TradeSignal
    {
        return TradeSignal::query()
            ->where('setup_key', $signal->setup_key)
            ->whereKeyNot($signal->id)
            ->latest('signal_generated_at')
            ->first();
    }
}
