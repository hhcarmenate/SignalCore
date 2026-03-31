<?php

namespace App\Support\Options;

use App\Enums\DataProvider;
use App\Models\OptionChainSnapshot;
use App\Models\OptionContract;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class OptionChainSnapshotSync
{
    /**
     * @param  array<int, array<string, mixed>>  $snapshots
     * @return Collection<int, OptionChainSnapshot>
     */
    public function sync(OptionContract $contract, array $snapshots): Collection
    {
        return collect($snapshots)
            ->map(fn (array $payload) => $this->upsertSnapshot($contract, $payload));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function upsertSnapshot(OptionContract $contract, array $payload): OptionChainSnapshot
    {
        $provider = DataProvider::from((string) Arr::get($payload, 'provider', DataProvider::TwelveData->value));
        $snapshotAt = CarbonImmutable::parse((string) Arr::get($payload, 'snapshot_at'));

        $snapshot = OptionChainSnapshot::query()->firstOrNew([
            'option_contract_id' => $contract->id,
            'provider' => $provider,
            'snapshot_at' => $snapshotAt,
        ]);

        $snapshot->fill([
            'bid_price' => Arr::get($payload, 'bid_price'),
            'ask_price' => Arr::get($payload, 'ask_price'),
            'mark_price' => Arr::get($payload, 'mark_price'),
            'last_price' => Arr::get($payload, 'last_price'),
            'volume' => Arr::get($payload, 'volume'),
            'open_interest' => Arr::get($payload, 'open_interest'),
            'implied_volatility' => Arr::get($payload, 'implied_volatility'),
            'provider_snapshot_id' => Arr::get($payload, 'provider_snapshot_id'),
            'provider_metadata' => Arr::get($payload, 'provider_metadata'),
            'is_stale' => (bool) Arr::get($payload, 'is_stale', false),
        ]);

        $snapshot->optionContract()->associate($contract);
        $snapshot->save();

        return $snapshot->fresh();
    }
}
