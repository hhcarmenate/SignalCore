<?php

namespace Tests\Feature;

use App\Enums\OptionContractType;
use App\Models\OptionChainSnapshot;
use App\Models\OptionContract;
use App\Models\Symbol;
use App\Support\Options\OptionContractEligibilityFilter;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OptionContractEligibilityFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_accepts_a_contract_that_passes_all_default_gates(): void
    {
        $contract = $this->makeContract(220, '2026-05-15');
        $snapshot = $this->makeSnapshot($contract, 2.10, 2.30, 1200, 5000);

        $result = app(OptionContractEligibilityFilter::class)->evaluate($contract, $snapshot, [], 215);

        $this->assertTrue($result['eligible']);
        $this->assertSame([], $result['reasons']);
        $this->assertSame(0.2, $result['spread']);
    }

    public function test_it_rejects_missing_bid_ask_data_by_default(): void
    {
        $contract = $this->makeContract();
        $snapshot = $this->makeSnapshot($contract, null, null, 1200, 5000);

        $result = app(OptionContractEligibilityFilter::class)->evaluate($contract, $snapshot, [], 220);

        $this->assertFalse($result['eligible']);
        $this->assertContains('missing_bid_ask', $result['reasons']);
        $this->assertContains('missing_spread', $result['reasons']);
    }

    public function test_it_rejects_low_volume_and_low_open_interest(): void
    {
        $contract = $this->makeContract();
        $snapshot = $this->makeSnapshot($contract, 1.10, 1.30, 10, 100);

        $result = app(OptionContractEligibilityFilter::class)->evaluate($contract, $snapshot, [], 220);

        $this->assertFalse($result['eligible']);
        $this->assertContains('below_min_volume', $result['reasons']);
        $this->assertContains('below_min_open_interest', $result['reasons']);
    }

    public function test_it_rejects_spread_and_expiration_violations(): void
    {
        $contract = $this->makeContract(220, '2026-04-02');
        $snapshot = $this->makeSnapshot($contract, 1.00, 2.00, 1200, 5000);

        $result = app(OptionContractEligibilityFilter::class)->evaluate($contract, $snapshot, [
            'as_of' => CarbonImmutable::parse('2026-03-31T12:00:00Z'),
        ], 220);

        $this->assertFalse($result['eligible']);
        $this->assertContains('spread_too_wide', $result['reasons']);
        $this->assertContains('expiration_too_near', $result['reasons']);
    }

    public function test_it_rejects_strike_distance_out_of_bounds(): void
    {
        $contract = $this->makeContract(280, '2026-05-15');
        $snapshot = $this->makeSnapshot($contract, 2.10, 2.30, 1200, 5000);

        $result = app(OptionContractEligibilityFilter::class)->evaluate($contract, $snapshot, [], 220);

        $this->assertFalse($result['eligible']);
        $this->assertContains('strike_out_of_bounds', $result['reasons']);
    }

    private function makeContract(float $strike = 220, string $expirationDate = '2026-05-15'): OptionContract
    {
        $underlying = Symbol::query()->create([
            'asset_type' => 'stock',
            'symbol' => fake()->unique()->lexify('OPT??'),
            'name' => 'Option Underlying',
            'market' => 'us_equities',
            'provider' => 'twelve_data',
            'provider_symbol' => fake()->unique()->lexify('OPT??'),
        ]);

        return OptionContract::query()->create([
            'underlying_symbol_id' => $underlying->id,
            'contract_symbol' => 'TEST_'.$expirationDate.'_C_'.$strike,
            'provider_contract_symbol' => str_replace(['-', '.'], '', 'TEST_'.$expirationDate.'_C_'.$strike),
            'option_type' => OptionContractType::Call,
            'strike_price' => $strike,
            'expiration_date' => $expirationDate,
            'provider' => 'twelve_data',
        ]);
    }

    private function makeSnapshot(OptionContract $contract, ?float $bid, ?float $ask, int $volume, int $openInterest): OptionChainSnapshot
    {
        return OptionChainSnapshot::query()->create([
            'option_contract_id' => $contract->id,
            'snapshot_at' => '2026-03-31T17:15:00Z',
            'bid_price' => $bid,
            'ask_price' => $ask,
            'mark_price' => ($bid !== null && $ask !== null) ? (($bid + $ask) / 2) : null,
            'volume' => $volume,
            'open_interest' => $openInterest,
            'provider' => 'twelve_data',
        ]);
    }
}
