<?php

namespace App\Support\Options;

use App\Models\OptionChainSnapshot;
use App\Models\OptionContract;
use Carbon\CarbonImmutable;

class OptionContractEligibilityFilter
{
    /**
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     */
    public function evaluate(OptionContract $contract, ?OptionChainSnapshot $snapshot = null, array $rules = [], ?float $underlyingPrice = null): array
    {
        $snapshot ??= $contract->snapshots()->latest('snapshot_at')->first();

        $rules = array_merge([
            'min_volume' => 100,
            'min_open_interest' => 500,
            'max_spread' => 0.50,
            'min_days_to_expiration' => 7,
            'max_days_to_expiration' => 90,
            'max_strike_distance_pct' => 0.10,
            'as_of' => CarbonImmutable::parse('2026-03-31T12:00:00Z'),
        ], $rules);

        $reasons = [];

        if (! $contract->is_active || $contract->status->value !== 'active') {
            $reasons[] = 'contract_inactive';
        }

        if ($snapshot === null) {
            $reasons[] = 'missing_snapshot';

            return $this->result(false, $reasons, null, null, null);
        }

        if ($snapshot->bid_price === null || $snapshot->ask_price === null) {
            $reasons[] = 'missing_bid_ask';
        }

        $spread = ($snapshot->bid_price !== null && $snapshot->ask_price !== null)
            ? round((float) $snapshot->ask_price - (float) $snapshot->bid_price, 8)
            : null;

        if (($snapshot->volume ?? 0) < $rules['min_volume']) {
            $reasons[] = 'below_min_volume';
        }

        if (($snapshot->open_interest ?? 0) < $rules['min_open_interest']) {
            $reasons[] = 'below_min_open_interest';
        }

        if ($spread === null) {
            $reasons[] = 'missing_spread';
        } elseif ($spread > $rules['max_spread']) {
            $reasons[] = 'spread_too_wide';
        }

        $daysToExpiration = $rules['as_of']->startOfDay()->diffInDays($contract->expiration_date, false);

        if ($daysToExpiration < $rules['min_days_to_expiration']) {
            $reasons[] = 'expiration_too_near';
        }

        if ($daysToExpiration > $rules['max_days_to_expiration']) {
            $reasons[] = 'expiration_too_far';
        }

        $strikeDistancePct = null;

        if ($underlyingPrice !== null && $underlyingPrice > 0) {
            $strikeDistancePct = round(abs(((float) $contract->strike_price - $underlyingPrice) / $underlyingPrice), 8);

            if ($strikeDistancePct > $rules['max_strike_distance_pct']) {
                $reasons[] = 'strike_out_of_bounds';
            }
        }

        return $this->result($reasons === [], $reasons, $spread, $daysToExpiration, $strikeDistancePct);
    }

    /**
     * @return array<string, mixed>
     */
    private function result(bool $eligible, array $reasons, ?float $spread, ?int $daysToExpiration, ?float $strikeDistancePct): array
    {
        return [
            'eligible' => $eligible,
            'reasons' => array_values(array_unique($reasons)),
            'spread' => $spread,
            'days_to_expiration' => $daysToExpiration,
            'strike_distance_pct' => $strikeDistancePct,
        ];
    }
}
