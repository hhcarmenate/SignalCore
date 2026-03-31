<?php

namespace Tests\Feature;

use App\Models\OptionChainSnapshot;
use App\Models\OptionContract;
use App\Models\Symbol;
use App\Support\Options\OptionChainSnapshotSync;
use App\Support\Options\OptionContractSync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OptionDataIngestionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_syncs_option_contracts_idempotently(): void
    {
        $underlying = $this->makeUnderlying();
        $service = app(OptionContractSync::class);

        $service->sync($underlying, [[
            'contract_symbol' => 'AAPL_20260619_C_220',
            'provider_contract_symbol' => 'AAPL260619C00220000',
            'option_type' => 'call',
            'strike_price' => 220,
            'expiration_date' => '2026-06-19',
            'provider' => 'twelve_data',
            'is_active' => true,
            'status' => 'active',
        ]]);

        $service->sync($underlying, [[
            'contract_symbol' => 'AAPL_20260619_C_220',
            'provider_contract_symbol' => 'AAPL260619C00220000',
            'option_type' => 'call',
            'strike_price' => 220,
            'expiration_date' => '2026-06-19',
            'provider' => 'twelve_data',
            'is_active' => false,
            'status' => 'inactive',
        ]]);

        $this->assertDatabaseCount('option_contracts', 1);
        $this->assertDatabaseHas('option_contracts', [
            'contract_symbol' => 'AAPL_20260619_C_220',
            'status' => 'inactive',
            'is_active' => false,
        ]);
    }

    public function test_it_appends_or_updates_snapshots_by_time_aware_identity(): void
    {
        $contract = $this->makeContract();
        $service = app(OptionChainSnapshotSync::class);

        $service->sync($contract, [[
            'snapshot_at' => '2026-03-31T17:00:00Z',
            'bid_price' => 2.10,
            'ask_price' => 2.30,
            'mark_price' => 2.20,
            'provider' => 'twelve_data',
            'volume' => 1000,
            'open_interest' => 5000,
        ]]);

        $service->sync($contract, [[
            'snapshot_at' => '2026-03-31T17:00:00Z',
            'bid_price' => 2.15,
            'ask_price' => 2.35,
            'mark_price' => 2.25,
            'provider' => 'twelve_data',
            'volume' => 1200,
            'open_interest' => 5200,
        ]]);

        $service->sync($contract, [[
            'snapshot_at' => '2026-03-31T17:05:00Z',
            'bid_price' => 2.18,
            'ask_price' => 2.38,
            'mark_price' => 2.28,
            'provider' => 'twelve_data',
            'volume' => 1400,
            'open_interest' => 5300,
        ]]);

        $this->assertDatabaseCount('option_chain_snapshots', 2);
        $this->assertDatabaseHas('option_chain_snapshots', [
            'option_contract_id' => $contract->id,
            'volume' => 1200,
            'open_interest' => 5200,
        ]);
    }

    public function test_it_keeps_contracts_and_snapshots_separate_in_the_workflow(): void
    {
        $underlying = $this->makeUnderlying('TSLA');
        $contract = app(OptionContractSync::class)->sync($underlying, [[
            'contract_symbol' => 'TSLA_20260717_P_180',
            'provider_contract_symbol' => 'TSLA260717P00180000',
            'option_type' => 'put',
            'strike_price' => 180,
            'expiration_date' => '2026-07-17',
            'provider' => 'twelve_data',
            'is_active' => true,
            'status' => 'active',
        ]])->first();

        app(OptionChainSnapshotSync::class)->sync($contract, [[
            'snapshot_at' => '2026-03-31T18:00:00Z',
            'bid_price' => 4.80,
            'ask_price' => 5.05,
            'mark_price' => 4.92,
            'provider' => 'twelve_data',
        ]]);

        $this->assertInstanceOf(OptionContract::class, $contract);
        $this->assertInstanceOf(OptionChainSnapshot::class, $contract->fresh()->snapshots->first());
        $this->assertSame('TSLA_20260717_P_180', $contract->contract_symbol);
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

    private function makeContract(): OptionContract
    {
        return app(OptionContractSync::class)->sync($this->makeUnderlying(), [[
            'contract_symbol' => 'AAPL_20260619_C_220',
            'provider_contract_symbol' => 'AAPL260619C00220000',
            'option_type' => 'call',
            'strike_price' => 220,
            'expiration_date' => '2026-06-19',
            'provider' => 'twelve_data',
            'is_active' => true,
            'status' => 'active',
        ]])->first();
    }
}
