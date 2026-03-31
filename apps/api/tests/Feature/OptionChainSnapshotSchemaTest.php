<?php

namespace Tests\Feature;

use App\Enums\OptionContractType;
use App\Models\OptionChainSnapshot;
use App\Models\OptionContract;
use App\Models\Symbol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class OptionChainSnapshotSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_persists_option_chain_snapshots_with_required_market_fields(): void
    {
        $contract = $this->makeContract();

        $snapshot = OptionChainSnapshot::query()->create([
            'option_contract_id' => $contract->id,
            'snapshot_at' => '2026-03-31T17:15:00Z',
            'bid_price' => 2.15,
            'ask_price' => 2.35,
            'mark_price' => 2.25,
            'last_price' => 2.2,
            'volume' => 1450,
            'open_interest' => 8200,
            'implied_volatility' => 0.345,
            'provider' => 'twelve_data',
            'provider_snapshot_id' => 'snap_123',
            'provider_metadata' => [
                'exchange' => 'OPRA',
            ],
            'is_stale' => false,
        ]);

        $this->assertSame($contract->id, $snapshot->option_contract_id);
        $this->assertInstanceOf(CarbonImmutable::class, $snapshot->snapshot_at);
        $this->assertSame('2.15000000', $snapshot->bid_price);
        $this->assertSame('2.35000000', $snapshot->ask_price);
        $this->assertSame('2.25000000', $snapshot->mark_price);
        $this->assertSame('2.20000000', $snapshot->last_price);
        $this->assertSame(1450, $snapshot->volume);
        $this->assertSame(8200, $snapshot->open_interest);
        $this->assertSame('0.34500000', $snapshot->implied_volatility);
        $this->assertFalse($snapshot->is_stale);
        $this->assertSame('OPRA', $snapshot->provider_metadata['exchange']);
    }

    public function test_it_exposes_relationships_between_contracts_and_snapshots(): void
    {
        $contract = $this->makeContract('TSLA_20260717_P_180', 'TSLA');
        $snapshot = OptionChainSnapshot::query()->create([
            'option_contract_id' => $contract->id,
            'snapshot_at' => '2026-03-31T18:00:00Z',
            'provider' => 'twelve_data',
        ]);

        $this->assertTrue($contract->snapshots->contains($snapshot));
        $this->assertSame($contract->id, $snapshot->optionContract->id);
    }

    private function makeContract(string $contractSymbol = 'AAPL_20260619_C_220', string $underlyingTicker = 'AAPL'): OptionContract
    {
        $underlying = Symbol::query()->create([
            'asset_type' => 'stock',
            'symbol' => $underlyingTicker,
            'name' => $underlyingTicker.' Underlying',
            'market' => 'us_equities',
            'provider' => 'twelve_data',
            'provider_symbol' => $underlyingTicker,
        ]);

        return OptionContract::query()->create([
            'underlying_symbol_id' => $underlying->id,
            'contract_symbol' => $contractSymbol,
            'provider_contract_symbol' => str_replace(['_', '.'], '', $contractSymbol),
            'option_type' => str_contains($contractSymbol, '_P_') ? OptionContractType::Put : OptionContractType::Call,
            'strike_price' => str_contains($contractSymbol, '_P_') ? 180 : 220,
            'expiration_date' => str_contains($contractSymbol, '20260717') ? '2026-07-17' : '2026-06-19',
            'provider' => 'twelve_data',
        ]);
    }
}
