<?php

namespace Tests\Feature;

use App\Enums\OptionContractStatus;
use App\Enums\OptionContractType;
use App\Enums\OptionExerciseStyle;
use App\Models\OptionContract;
use App\Models\Symbol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OptionContractSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_persists_option_contracts_with_required_identity_fields(): void
    {
        $underlying = $this->makeUnderlying();

        $contract = OptionContract::query()->create([
            'underlying_symbol_id' => $underlying->id,
            'contract_symbol' => 'AAPL_20260619_C_220',
            'provider_contract_symbol' => 'AAPL260619C00220000',
            'option_type' => OptionContractType::Call,
            'strike_price' => 220,
            'expiration_date' => '2026-06-19',
            'status' => OptionContractStatus::Active,
            'exercise_style' => OptionExerciseStyle::American,
            'provider' => 'twelve_data',
            'provider_metadata' => [
                'exchange' => 'OPRA',
            ],
        ]);

        $this->assertSame($underlying->id, $contract->underlying_symbol_id);
        $this->assertSame(OptionContractType::Call, $contract->option_type);
        $this->assertSame(OptionContractStatus::Active, $contract->status);
        $this->assertSame(OptionExerciseStyle::American, $contract->exercise_style);
        $this->assertSame('220.00000000', $contract->strike_price);
        $this->assertInstanceOf(Carbon::class, $contract->expiration_date);
        $this->assertTrue($contract->is_active);
        $this->assertSame(100, $contract->multiplier);
        $this->assertSame(100, $contract->shares_per_contract);
        $this->assertSame('OPRA', $contract->provider_metadata['exchange']);
    }

    public function test_it_exposes_relationships_between_underlying_and_option_contracts(): void
    {
        $underlying = $this->makeUnderlying('TSLA');
        $contract = OptionContract::query()->create([
            'underlying_symbol_id' => $underlying->id,
            'contract_symbol' => 'TSLA_20260717_P_180',
            'provider_contract_symbol' => 'TSLA260717P00180000',
            'option_type' => OptionContractType::Put,
            'strike_price' => 180,
            'expiration_date' => '2026-07-17',
            'provider' => 'twelve_data',
        ]);

        $this->assertTrue($underlying->optionContracts->contains($contract));
        $this->assertSame($underlying->id, $contract->underlyingSymbol->id);
    }

    private function makeUnderlying(string $symbol = 'AAPL'): Symbol
    {
        return Symbol::query()->create([
            'asset_type' => 'stock',
            'symbol' => $symbol,
            'name' => $symbol.' Underlying',
            'market' => 'us_equities',
            'provider' => 'twelve_data',
            'provider_symbol' => $symbol,
        ]);
    }
}
