<?php

namespace App\Support\Signals;

use App\Models\TradeSignalOutcome;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TradeSignalStrategyComparison
{
    public function __construct(
        private readonly TradeSignalPerformanceMetrics $performanceMetrics,
    ) {
    }

    /**
     * @param  Builder<TradeSignalOutcome>|null  $query
     * @return Collection<int, array<string, mixed>>
     */
    public function leaderboard(?Builder $query = null): Collection
    {
        return $this->baseQuery($query)
            ->get()
            ->groupBy(fn (TradeSignalOutcome $outcome) => $outcome->tradeSignal?->strategy_key)
            ->filter(fn (Collection $group, $strategyKey) => filled($strategyKey))
            ->map(function (Collection $group, string $strategyKey): array {
                $summary = $this->performanceMetrics->summarize($this->queryForIds($group->pluck('id')));

                return [
                    'strategy_key' => $strategyKey,
                    ...$summary,
                    'bullish_signal_count' => $group->filter(fn (TradeSignalOutcome $outcome) => $outcome->tradeSignal?->direction === 'bullish')->count(),
                    'bearish_signal_count' => $group->filter(fn (TradeSignalOutcome $outcome) => $outcome->tradeSignal?->direction === 'bearish')->count(),
                    'sample_size_note' => $this->sampleSizeNote($summary['signal_count']),
                    'comparison_score' => $this->comparisonScore($summary),
                ];
            })
            ->sortByDesc('comparison_score')
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    public function detail(string $strategyKey): array
    {
        $query = $this->baseQuery()->whereHas('tradeSignal', fn (Builder $builder) => $builder->where('strategy_key', $strategyKey));
        $summary = $this->performanceMetrics->summarize(clone $query);
        $outcomes = (clone $query)->get();

        return [
            'strategy_key' => $strategyKey,
            ...$summary,
            'bullish_signal_count' => $outcomes->filter(fn (TradeSignalOutcome $outcome) => $outcomes->first()?->tradeSignal && $outcome->tradeSignal?->direction === 'bullish')->count(),
            'bearish_signal_count' => $outcomes->filter(fn (TradeSignalOutcome $outcome) => $outcome->tradeSignal?->direction === 'bearish')->count(),
            'sample_size_note' => $this->sampleSizeNote($summary['signal_count']),
            'symbol_breakdown' => $this->performanceMetrics->bySymbol(clone $query)->all(),
            'timeframe_breakdown' => $this->performanceMetrics->byTimeframe(clone $query)->all(),
            'direction_breakdown' => $this->directionBreakdown($outcomes),
        ];
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

    private function queryForIds(Collection $ids): Builder
    {
        return TradeSignalOutcome::query()->whereIn('id', $ids->all());
    }

    /**
     * @param  array<string, mixed>  $summary
     */
    private function comparisonScore(array $summary): float
    {
        $signalCountWeight = min((int) $summary['signal_count'], 20) * 0.5;
        $winRateWeight = ($summary['win_rate'] ?? 0) * 0.4;
        $averageRWeight = (($summary['average_r_multiple'] ?? 0) + 1) * 20;

        return round($signalCountWeight + $winRateWeight + $averageRWeight, 2);
    }

    private function sampleSizeNote(int $signalCount): string
    {
        return match (true) {
            $signalCount < 5 => 'Very low sample size; treat results as provisional.',
            $signalCount < 10 => 'Low sample size; compare carefully before drawing conclusions.',
            $signalCount < 20 => 'Moderate sample size; useful, but still maturing.',
            default => 'Healthy sample size for comparison purposes.',
        };
    }

    /**
     * @param  Collection<int, TradeSignalOutcome>  $outcomes
     * @return array<int, array<string, mixed>>
     */
    private function directionBreakdown(Collection $outcomes): array
    {
        return $outcomes
            ->groupBy(fn (TradeSignalOutcome $outcome) => $outcome->tradeSignal?->direction)
            ->filter(fn (Collection $group, $direction) => filled($direction))
            ->map(function (Collection $group, string $direction): array {
                $summary = $this->performanceMetrics->summarize($this->queryForIds($group->pluck('id')));

                return [
                    'direction' => $direction,
                    ...$summary,
                ];
            })
            ->values()
            ->all();
    }
}
