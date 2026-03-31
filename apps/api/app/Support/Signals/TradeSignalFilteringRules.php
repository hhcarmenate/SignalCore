<?php

namespace App\Support\Signals;

use App\Models\TradeSignal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TradeSignalFilteringRules
{
    public function classifyReviewPriority(TradeSignal $signal): string
    {
        if ($signal->ranking_score >= 85 || ($signal->score >= 85 && $signal->confidence >= 80)) {
            return 'high';
        }

        if ($signal->ranking_score >= 70 || ($signal->score >= 70 && $signal->confidence >= 65)) {
            return 'medium';
        }

        return 'low';
    }

    public function computeReviewScore(TradeSignal $signal): float
    {
        return round((float) ($signal->ranking_score ?? $signal->score), 2);
    }

    public function apply(TradeSignal $signal): TradeSignal
    {
        $signal->forceFill([
            'review_priority' => $this->classifyReviewPriority($signal),
            'review_score' => $this->computeReviewScore($signal),
        ])->save();

        return $signal->refresh();
    }

    /**
     * @param  array{strategy_keys?: array<int,string>, directions?: array<int,string>, timeframes?: array<int,string>}  $filters
     */
    public function applyFilters(Builder $query, array $filters = []): Builder
    {
        if (! empty($filters['strategy_keys'])) {
            $query->whereIn('strategy_key', $filters['strategy_keys']);
        }

        if (! empty($filters['directions'])) {
            $query->whereIn('direction', $filters['directions']);
        }

        if (! empty($filters['timeframes'])) {
            $query->whereIn('timeframe', $filters['timeframes']);
        }

        return $query;
    }

    /**
     * @param  array{strategy_keys?: array<int,string>, directions?: array<int,string>, timeframes?: array<int,string>}  $filters
     */
    public function reviewQueue(array $filters = []): Collection
    {
        $query = TradeSignal::query()
            ->whereIn('status', ['new', 'pending_review'])
            ->orderByRaw("CASE review_priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
            ->orderByDesc('review_score')
            ->orderByDesc('signal_generated_at');

        return $this->applyFilters($query, $filters)->get();
    }
}
