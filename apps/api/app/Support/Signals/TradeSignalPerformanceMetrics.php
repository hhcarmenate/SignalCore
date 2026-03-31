<?php

namespace App\Support\Signals;

use App\Enums\TradeSignalOutcomeLabel;
use App\Enums\TradeSignalOutcomeState;
use App\Models\TradeSignalOutcome;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TradeSignalPerformanceMetrics
{
    /**
     * @param  Builder<TradeSignalOutcome>|null  $query
     * @return array<string, mixed>
     */
    public function summarize(?Builder $query = null): array
    {
        $outcomes = $this->baseQuery($query)->get();

        return $this->buildSummary($outcomes);
    }

    /**
     * @param  Builder<TradeSignalOutcome>|null  $query
     * @return Collection<int, array<string, mixed>>
     */
    public function bySymbol(?Builder $query = null): Collection
    {
        return $this->baseQuery($query)
            ->get()
            ->groupBy(fn (TradeSignalOutcome $outcome) => $outcome->tradeSignal?->symbol?->symbol)
            ->filter(fn (Collection $group, $symbol) => filled($symbol))
            ->map(fn (Collection $group, string $symbol) => [
                'symbol' => $symbol,
                ...$this->buildSummary($group),
                'last_signal_at' => optional(
                    $group
                        ->map(fn (TradeSignalOutcome $outcome) => $outcome->tradeSignal?->signal_generated_at)
                        ->filter()
                        ->sortDesc()
                        ->first()
                )?->toISOString(),
            ])
            ->sortBy('symbol')
            ->values();
    }

    /**
     * @param  Builder<TradeSignalOutcome>|null  $query
     * @return Collection<int, array<string, mixed>>
     */
    public function byTimeframe(?Builder $query = null): Collection
    {
        return $this->baseQuery($query)
            ->get()
            ->groupBy(fn (TradeSignalOutcome $outcome) => $outcome->tradeSignal?->timeframe)
            ->filter(fn (Collection $group, $timeframe) => filled($timeframe))
            ->map(fn (Collection $group, string $timeframe) => [
                'timeframe' => $timeframe,
                ...$this->buildSummary($group),
            ])
            ->sortBy('timeframe')
            ->values();
    }

    /**
     * @param  Builder<TradeSignalOutcome>|null  $query
     * @return Builder<TradeSignalOutcome>
     */
    private function baseQuery(?Builder $query = null): Builder
    {
        return ($query ?? TradeSignalOutcome::query())
            ->with('tradeSignal.symbol');
    }

    /**
     * @param  Collection<int, TradeSignalOutcome>  $outcomes
     * @return array<string, mixed>
     */
    private function buildSummary(Collection $outcomes): array
    {
        $signalCount = $outcomes->count();
        $resolved = $outcomes->filter(fn (TradeSignalOutcome $outcome) => in_array($outcome->outcome_label, [
            TradeSignalOutcomeLabel::Win,
            TradeSignalOutcomeLabel::Loss,
        ], true));
        $entered = $outcomes->filter(fn (TradeSignalOutcome $outcome) => in_array($outcome->evaluation_state, [
            TradeSignalOutcomeState::Entered,
            TradeSignalOutcomeState::TargetHit,
            TradeSignalOutcomeState::StopHit,
            TradeSignalOutcomeState::ExpiredAfterEntry,
            TradeSignalOutcomeState::AmbiguousSameBar,
        ], true));

        $wins = $resolved->where('outcome_label', TradeSignalOutcomeLabel::Win)->count();
        $losses = $resolved->where('outcome_label', TradeSignalOutcomeLabel::Loss)->count();
        $targetHits = $outcomes->where('target_hit', true)->count();
        $stopHits = $outcomes->where('stop_hit', true)->count();
        $unresolvedCount = $outcomes->where('outcome_label', TradeSignalOutcomeLabel::Unresolved)->count();
        $neutralCount = $outcomes->where('outcome_label', TradeSignalOutcomeLabel::Neutral)->count();

        $realizedRValues = $resolved
            ->map(function (TradeSignalOutcome $outcome): ?float {
                return match ($outcome->outcome_label) {
                    TradeSignalOutcomeLabel::Win => 1.0,
                    TradeSignalOutcomeLabel::Loss => -1.0,
                    default => null,
                };
            })
            ->filter(fn (?float $value) => $value !== null)
            ->values();

        return [
            'signal_count' => $signalCount,
            'resolved_count' => $resolved->count(),
            'entered_count' => $entered->count(),
            'win_count' => $wins,
            'loss_count' => $losses,
            'neutral_count' => $neutralCount,
            'unresolved_count' => $unresolvedCount,
            'target_hit_count' => $targetHits,
            'stop_hit_count' => $stopHits,
            'win_rate' => $this->safeRate($wins, $resolved->count()),
            'target_hit_rate' => $this->safeRate($targetHits, $entered->count()),
            'stop_hit_rate' => $this->safeRate($stopHits, $entered->count()),
            'average_r_multiple' => $realizedRValues->isEmpty()
                ? null
                : round($realizedRValues->avg(), 4),
        ];
    }

    private function safeRate(int $numerator, int $denominator): ?float
    {
        if ($denominator === 0) {
            return null;
        }

        return round(($numerator / $denominator) * 100, 2);
    }
}
