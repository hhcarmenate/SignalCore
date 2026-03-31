<?php

namespace App\Support\Signals;

use App\Enums\TradeSignalOutcomeLabel;
use App\Enums\TradeSignalOutcomeState;
use App\Models\Candle;
use App\Models\TradeSignal;
use App\Models\TradeSignalOutcome;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

class TradeSignalEvaluationWorkflow
{
    public function isReady(TradeSignal $signal, ?CarbonImmutable $asOf = null): bool
    {
        $asOf ??= CarbonImmutable::now('UTC');

        if (! $signal->signal_generated_at) {
            return false;
        }

        $evaluationEndsAt = $this->evaluationEndsAt($signal, $asOf);

        if ($evaluationEndsAt->lessThanOrEqualTo($signal->signal_generated_at)) {
            return false;
        }

        return $this->postSignalCandles($signal, $evaluationEndsAt)->isNotEmpty();
    }

    public function evaluate(TradeSignal $signal, ?CarbonImmutable $asOf = null): TradeSignalOutcome
    {
        $asOf ??= CarbonImmutable::now('UTC');

        $outcome = $signal->outcome()->firstOrNew();

        if ($signal->signal_generated_at === null) {
            return $this->markPending($signal, $outcome, 'missing_signal_generated_at');
        }

        $evaluationEndsAt = $this->evaluationEndsAt($signal, $asOf);
        $candles = $this->postSignalCandles($signal, $evaluationEndsAt);

        if ($candles->isEmpty()) {
            return $this->markPending($signal, $outcome, 'market_data_unavailable');
        }

        $startedAt = $candles->first()->bar_time ?? $signal->signal_generated_at;
        $entryCandle = $this->firstEntryCandle($signal, $candles);

        if ($entryCandle === null) {
            return $this->persistOutcome(
                signal: $signal,
                outcome: $outcome,
                attributes: [
                    'evaluation_state' => TradeSignalOutcomeState::EntryNotReached,
                    'outcome_label' => TradeSignalOutcomeLabel::Neutral,
                    'entry_reached' => false,
                    'target_hit' => false,
                    'stop_hit' => false,
                    'expired_without_entry' => true,
                    'expired_after_entry' => false,
                    'evaluation_started_at' => $startedAt,
                    'evaluation_completed_at' => $evaluationEndsAt,
                    'expired_at' => $signal->expires_at ?? $evaluationEndsAt,
                    'evaluation_assumption_key' => 'first_touch_v1',
                    'ambiguity_reason' => null,
                    'notes' => 'Evaluation completed without entry activation.',
                ],
            );
        }

        $postEntryCandles = $candles->filter(fn (Candle $candle) => $candle->bar_time->greaterThanOrEqualTo($entryCandle->bar_time))->values();
        $resolution = $this->resolveAfterEntry($signal, $postEntryCandles);

        if ($resolution['type'] === 'target_hit') {
            return $this->persistOutcome(
                signal: $signal,
                outcome: $outcome,
                attributes: [
                    'evaluation_state' => TradeSignalOutcomeState::TargetHit,
                    'outcome_label' => TradeSignalOutcomeLabel::Win,
                    'entry_reached' => true,
                    'entry_reached_at' => $entryCandle->bar_time,
                    'target_hit' => true,
                    'target_hit_at' => $resolution['at'],
                    'stop_hit' => false,
                    'expired_without_entry' => false,
                    'expired_after_entry' => false,
                    'evaluation_started_at' => $startedAt,
                    'evaluation_completed_at' => $resolution['at'],
                    'expired_at' => $signal->expires_at,
                    'evaluation_assumption_key' => 'first_touch_v1',
                    'ambiguity_reason' => null,
                    'notes' => 'Resolved via target hit after entry activation.',
                ],
            );
        }

        if ($resolution['type'] === 'stop_hit') {
            return $this->persistOutcome(
                signal: $signal,
                outcome: $outcome,
                attributes: [
                    'evaluation_state' => TradeSignalOutcomeState::StopHit,
                    'outcome_label' => TradeSignalOutcomeLabel::Loss,
                    'entry_reached' => true,
                    'entry_reached_at' => $entryCandle->bar_time,
                    'target_hit' => false,
                    'stop_hit' => true,
                    'stop_hit_at' => $resolution['at'],
                    'expired_without_entry' => false,
                    'expired_after_entry' => false,
                    'evaluation_started_at' => $startedAt,
                    'evaluation_completed_at' => $resolution['at'],
                    'expired_at' => $signal->expires_at,
                    'evaluation_assumption_key' => 'first_touch_v1',
                    'ambiguity_reason' => null,
                    'notes' => 'Resolved via stop hit after entry activation.',
                ],
            );
        }

        if ($resolution['type'] === 'ambiguous_same_bar') {
            return $this->persistOutcome(
                signal: $signal,
                outcome: $outcome,
                attributes: [
                    'evaluation_state' => TradeSignalOutcomeState::AmbiguousSameBar,
                    'outcome_label' => TradeSignalOutcomeLabel::Neutral,
                    'entry_reached' => true,
                    'entry_reached_at' => $entryCandle->bar_time,
                    'target_hit' => true,
                    'target_hit_at' => $resolution['at'],
                    'stop_hit' => true,
                    'stop_hit_at' => $resolution['at'],
                    'expired_without_entry' => false,
                    'expired_after_entry' => false,
                    'evaluation_started_at' => $startedAt,
                    'evaluation_completed_at' => $resolution['at'],
                    'expired_at' => $signal->expires_at,
                    'evaluation_assumption_key' => 'first_touch_v1',
                    'ambiguity_reason' => 'ambiguous_same_bar',
                    'notes' => 'Target and stop were both reachable in the same bar after entry.',
                ],
            );
        }

        return $this->persistOutcome(
            signal: $signal,
            outcome: $outcome,
            attributes: [
                'evaluation_state' => TradeSignalOutcomeState::ExpiredAfterEntry,
                'outcome_label' => TradeSignalOutcomeLabel::Neutral,
                'entry_reached' => true,
                'entry_reached_at' => $entryCandle->bar_time,
                'target_hit' => false,
                'stop_hit' => false,
                'expired_without_entry' => false,
                'expired_after_entry' => true,
                'evaluation_started_at' => $startedAt,
                'evaluation_completed_at' => $evaluationEndsAt,
                'expired_at' => $signal->expires_at ?? $evaluationEndsAt,
                'evaluation_assumption_key' => 'first_touch_v1',
                'ambiguity_reason' => null,
                'notes' => 'Entry activated but no decisive resolution occurred before evaluation end.',
            ],
        );
    }

    public function reevaluate(TradeSignal $signal, string $reason, ?CarbonImmutable $asOf = null): TradeSignalOutcome
    {
        $outcome = $this->evaluate($signal->fresh(['outcome']), $asOf);

        $notes = trim(($outcome->notes ? $outcome->notes.' ' : '').'Re-evaluated: '.$reason);
        $outcome->forceFill(['notes' => $notes])->save();

        return $outcome->fresh();
    }

    private function evaluationEndsAt(TradeSignal $signal, CarbonImmutable $asOf): CarbonImmutable
    {
        if ($signal->expires_at !== null) {
            return $signal->expires_at->lessThan($asOf) ? $signal->expires_at : $asOf;
        }

        $fallbackHours = match ($signal->timeframe) {
            '4h' => 24,
            '1d' => 72,
            default => 24,
        };

        return $signal->signal_generated_at->addHours($fallbackHours)->lessThan($asOf)
            ? $signal->signal_generated_at->addHours($fallbackHours)
            : $asOf;
    }

    /**
     * @return Collection<int, Candle>
     */
    private function postSignalCandles(TradeSignal $signal, CarbonImmutable $evaluationEndsAt): Collection
    {
        return Candle::query()
            ->where('symbol_id', $signal->symbol_id)
            ->where('timeframe', $signal->timeframe)
            ->where('bar_time', '>', $signal->signal_generated_at)
            ->where('bar_time', '<=', $evaluationEndsAt)
            ->orderBy('bar_time')
            ->get();
    }

    private function firstEntryCandle(TradeSignal $signal, Collection $candles): ?Candle
    {
        return $candles->first(fn (Candle $candle) => $this->touchesEntry($signal, $candle));
    }

    /**
     * @param  Collection<int, Candle>  $candles
     * @return array{type: string, at: ?CarbonImmutable}
     */
    private function resolveAfterEntry(TradeSignal $signal, Collection $candles): array
    {
        foreach ($candles as $candle) {
            $targetHit = $this->touchesTarget($signal, $candle);
            $stopHit = $this->touchesStop($signal, $candle);

            if ($targetHit && $stopHit) {
                return ['type' => 'ambiguous_same_bar', 'at' => $candle->bar_time];
            }

            if ($targetHit) {
                return ['type' => 'target_hit', 'at' => $candle->bar_time];
            }

            if ($stopHit) {
                return ['type' => 'stop_hit', 'at' => $candle->bar_time];
            }
        }

        return ['type' => 'expired_after_entry', 'at' => null];
    }

    private function touchesEntry(TradeSignal $signal, Candle $candle): bool
    {
        if ($signal->entry_price === null) {
            return false;
        }

        return $signal->direction === 'bullish'
            ? (float) $candle->high >= (float) $signal->entry_price
            : (float) $candle->low <= (float) $signal->entry_price;
    }

    private function touchesTarget(TradeSignal $signal, Candle $candle): bool
    {
        if ($signal->target_price === null) {
            return false;
        }

        return $signal->direction === 'bullish'
            ? (float) $candle->high >= (float) $signal->target_price
            : (float) $candle->low <= (float) $signal->target_price;
    }

    private function touchesStop(TradeSignal $signal, Candle $candle): bool
    {
        if ($signal->stop_loss === null) {
            return false;
        }

        return $signal->direction === 'bullish'
            ? (float) $candle->low <= (float) $signal->stop_loss
            : (float) $candle->high >= (float) $signal->stop_loss;
    }

    private function markPending(TradeSignal $signal, TradeSignalOutcome $outcome, string $reason): TradeSignalOutcome
    {
        return $this->persistOutcome(
            signal: $signal,
            outcome: $outcome,
            attributes: [
                'evaluation_state' => TradeSignalOutcomeState::Pending,
                'outcome_label' => TradeSignalOutcomeLabel::Unresolved,
                'entry_reached' => false,
                'target_hit' => false,
                'stop_hit' => false,
                'expired_without_entry' => false,
                'expired_after_entry' => false,
                'evaluation_assumption_key' => 'first_touch_v1',
                'ambiguity_reason' => null,
                'notes' => 'Evaluation pending: '.$reason,
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function persistOutcome(TradeSignal $signal, TradeSignalOutcome $outcome, array $attributes): TradeSignalOutcome
    {
        $outcome->trade_signal_id = $signal->id;
        $outcome->fill($attributes);
        $outcome->save();

        return $outcome->fresh();
    }
}
