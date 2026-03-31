<?php

namespace App\Support\Signals;

use App\Data\Signals\PersistTradeSignalData;
use App\Models\ScannerStrategy;
use App\Models\Symbol;
use App\Models\TradeSignal;
use Illuminate\Support\Arr;
use InvalidArgumentException;

class TradeSignalPersistenceContract
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function map(array $payload): PersistTradeSignalData
    {
        foreach (['symbol', 'strategy_key', 'timeframe', 'direction', 'signal_category', 'thesis'] as $field) {
            if (blank($payload[$field] ?? null)) {
                throw new InvalidArgumentException("Missing required signal payload field [{$field}].");
            }
        }

        $symbol = Symbol::query()->where('symbol', strtoupper((string) $payload['symbol']))->first();

        if (! $symbol) {
            throw new InvalidArgumentException('Cannot persist signal without a matching symbol record.');
        }

        $strategy = ScannerStrategy::query()->where('key', $payload['strategy_key'])->first();

        return new PersistTradeSignalData([
            'watchlist_id' => $payload['watchlist_id'] ?? null,
            'symbol_id' => $symbol->id,
            'scanner_strategy_id' => $strategy?->id,
            'strategy_key' => $payload['strategy_key'],
            'timeframe' => $payload['timeframe'],
            'direction' => $payload['direction'],
            'execution_hint' => $payload['execution_hint'] ?? null,
            'signal_category' => $payload['signal_category'],
            'thesis' => $payload['thesis'],
            'entry_price' => Arr::get($payload, 'levels.entry'),
            'stop_loss' => Arr::get($payload, 'levels.stop_loss'),
            'target_price' => Arr::get($payload, 'levels.target'),
            'score' => $payload['score'] ?? 0,
            'confidence' => $payload['confidence'] ?? 0,
            'ranking_score' => $payload['ranking_score'] ?? null,
            'ranking_position' => $payload['ranking_position'] ?? null,
            'score_breakdown' => $payload['score_breakdown'] ?? null,
            'indicator_snapshot' => $payload['indicators'] ?? null,
            'market_context' => $payload['context'] ?? null,
            'metadata' => $payload['metadata'] ?? null,
            'signal_generated_at' => $payload['signal_generated_at'] ?? now()->toIso8601String(),
            'source_run_reference' => $payload['source_run_reference'] ?? null,
            'source_signal_reference' => $payload['source_signal_reference'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function persist(array $payload): TradeSignal
    {
        $mapped = $this->map($payload);

        return TradeSignal::query()->create($mapped->attributes);
    }
}
