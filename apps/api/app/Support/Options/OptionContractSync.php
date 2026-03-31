<?php

namespace App\Support\Options;

use App\Enums\DataProvider;
use App\Enums\OptionContractStatus;
use App\Enums\OptionContractType;
use App\Enums\OptionExerciseStyle;
use App\Models\OptionContract;
use App\Models\Symbol;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class OptionContractSync
{
    /**
     * @param  array<int, array<string, mixed>>  $contracts
     * @return Collection<int, OptionContract>
     */
    public function sync(Symbol $underlying, array $contracts): Collection
    {
        return collect($contracts)
            ->map(fn (array $payload) => $this->upsertContract($underlying, $payload));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function upsertContract(Symbol $underlying, array $payload): OptionContract
    {
        $type = OptionContractType::from((string) Arr::get($payload, 'option_type'));
        $strike = (float) Arr::get($payload, 'strike_price');
        $expirationDate = CarbonImmutable::parse((string) Arr::get($payload, 'expiration_date'))->toDateString();
        $provider = DataProvider::from((string) Arr::get($payload, 'provider', DataProvider::TwelveData->value));

        $contract = OptionContract::query()->firstOrNew([
            'underlying_symbol_id' => $underlying->id,
            'option_type' => $type,
            'strike_price' => $strike,
            'expiration_date' => $expirationDate,
        ]);

        $contract->fill([
            'contract_symbol' => (string) Arr::get($payload, 'contract_symbol'),
            'provider_contract_symbol' => Arr::get($payload, 'provider_contract_symbol'),
            'is_active' => (bool) Arr::get($payload, 'is_active', true),
            'status' => OptionContractStatus::from((string) Arr::get($payload, 'status', OptionContractStatus::Active->value)),
            'multiplier' => (int) Arr::get($payload, 'multiplier', 100),
            'exercise_style' => OptionExerciseStyle::from((string) Arr::get($payload, 'exercise_style', OptionExerciseStyle::American->value)),
            'shares_per_contract' => (int) Arr::get($payload, 'shares_per_contract', 100),
            'provider' => $provider,
            'provider_metadata' => Arr::get($payload, 'provider_metadata'),
            'listed_at' => Arr::has($payload, 'listed_at') ? CarbonImmutable::parse((string) Arr::get($payload, 'listed_at')) : null,
            'delisted_at' => Arr::has($payload, 'delisted_at') ? CarbonImmutable::parse((string) Arr::get($payload, 'delisted_at')) : null,
        ]);

        $contract->underlyingSymbol()->associate($underlying);
        $contract->save();

        return $contract->fresh();
    }
}
