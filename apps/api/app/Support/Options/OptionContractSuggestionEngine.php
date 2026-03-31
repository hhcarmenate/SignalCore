<?php

namespace App\Support\Options;

use App\Enums\OptionContractType;
use App\Models\OptionContract;
use App\Models\TradeSignal;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class OptionContractSuggestionEngine
{
    public function __construct(
        private readonly OptionContractEligibilityFilter $eligibilityFilter,
    ) {
    }

    /**
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     */
    public function suggest(TradeSignal $signal, float $underlyingPrice, array $rules = []): array
    {
        $preferredType = $signal->direction === 'bullish'
            ? OptionContractType::Call
            : OptionContractType::Put;

        $contracts = OptionContract::query()
            ->with(['snapshots' => fn ($query) => $query->latest('snapshot_at')])
            ->where('underlying_symbol_id', $signal->symbol_id)
            ->where('option_type', $preferredType)
            ->get();

        $evaluated = $contracts
            ->map(function (OptionContract $contract) use ($rules, $underlyingPrice, $signal): array {
                $latestSnapshot = $contract->snapshots->sortByDesc('snapshot_at')->first();
                $eligibility = $this->eligibilityFilter->evaluate($contract, $latestSnapshot, $rules + [
                    'as_of' => $signal->signal_generated_at instanceof CarbonImmutable
                        ? $signal->signal_generated_at
                        : CarbonImmutable::parse($signal->signal_generated_at),
                ], $underlyingPrice);

                $daysToExpiration = $eligibility['days_to_expiration'];
                $strikeDistance = $eligibility['strike_distance_pct'];
                $spread = $eligibility['spread'];

                $score = $eligibility['eligible']
                    ? $this->score($daysToExpiration, $strikeDistance, $spread, $latestSnapshot?->volume, $latestSnapshot?->open_interest)
                    : null;

                return [
                    'contract' => $contract,
                    'snapshot' => $latestSnapshot,
                    'eligibility' => $eligibility,
                    'score' => $score,
                ];
            })
            ->filter(fn (array $row) => $row['eligibility']['eligible'])
            ->sortByDesc('score')
            ->values();

        $primary = $evaluated->first();
        $alternates = $evaluated->slice(1, 2)->values();

        return [
            'signal_id' => $signal->id,
            'direction' => $signal->direction,
            'preferred_option_type' => $preferredType->value,
            'primary_suggestion' => $primary ? $this->formatSuggestion($primary, true) : null,
            'alternate_suggestions' => $alternates->map(fn (array $row) => $this->formatSuggestion($row, false))->all(),
            'candidate_count' => $evaluated->count(),
        ];
    }

    private function score(?int $daysToExpiration, ?float $strikeDistancePct, ?float $spread, ?int $volume, ?int $openInterest): float
    {
        $dteScore = $daysToExpiration === null ? 0 : max(0, 100 - abs($daysToExpiration - 30));
        $strikeScore = $strikeDistancePct === null ? 0 : max(0, 100 - ($strikeDistancePct * 500));
        $spreadScore = $spread === null ? 0 : max(0, 100 - ($spread * 100));
        $liquidityScore = min((int) ($volume ?? 0), 5000) / 50 + min((int) ($openInterest ?? 0), 10000) / 100;

        return round($dteScore * 0.35 + $strikeScore * 0.30 + $spreadScore * 0.20 + $liquidityScore * 0.15, 2);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function formatSuggestion(array $row, bool $primary): array
    {
        /** @var OptionContract $contract */
        $contract = $row['contract'];
        $snapshot = $row['snapshot'];
        $eligibility = $row['eligibility'];

        $rationale = [
            'direction_to_contract_mapping',
            'passed_liquidity_filters',
            'expiration_within_window',
            'strike_within_bounds',
        ];

        if ($primary) {
            $rationale[] = 'highest_ranked_candidate';
        }

        return [
            'option_contract_id' => $contract->id,
            'contract_symbol' => $contract->contract_symbol,
            'option_type' => $contract->option_type->value,
            'strike_price' => $contract->strike_price,
            'expiration_date' => $contract->expiration_date?->toDateString(),
            'bid_price' => $snapshot?->bid_price,
            'ask_price' => $snapshot?->ask_price,
            'mark_price' => $snapshot?->mark_price,
            'volume' => $snapshot?->volume,
            'open_interest' => $snapshot?->open_interest,
            'implied_volatility' => $snapshot?->implied_volatility,
            'score' => $row['score'],
            'rationale' => $rationale,
            'eligibility_summary' => $eligibility,
        ];
    }
}
