<?php

namespace Tests\Feature;

use App\Models\Symbol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SymbolSearchApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_symbols_with_default_limit(): void
    {
        Symbol::query()->create([
            'asset_type' => 'stock',
            'symbol' => 'AAPL',
            'name' => 'Apple Inc.',
            'market' => 'us_equities',
            'status' => 'active',
            'provider' => 'manual',
            'provider_symbol' => 'AAPL',
        ]);

        Symbol::query()->create([
            'asset_type' => 'etf',
            'symbol' => 'SPY',
            'name' => 'SPDR S&P 500 ETF Trust',
            'market' => 'us_equities',
            'status' => 'active',
            'provider' => 'manual',
            'provider_symbol' => 'SPY',
        ]);

        $response = $this->getJson('/api/symbols');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.symbol', 'AAPL')
            ->assertJsonPath('data.1.symbol', 'SPY');
    }

    public function test_it_prioritizes_exact_symbol_matches(): void
    {
        Symbol::query()->create([
            'asset_type' => 'etf',
            'symbol' => 'QQQ',
            'name' => 'Invesco QQQ Trust',
            'market' => 'us_equities',
            'status' => 'active',
            'provider' => 'manual',
            'provider_symbol' => 'QQQ',
        ]);

        Symbol::query()->create([
            'asset_type' => 'stock',
            'symbol' => 'QCOM',
            'name' => 'QUALCOMM Incorporated',
            'market' => 'us_equities',
            'status' => 'active',
            'provider' => 'manual',
            'provider_symbol' => 'QCOM',
        ]);

        $response = $this->getJson('/api/symbols?search=QQQ');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.symbol', 'QQQ');
    }

    public function test_it_supports_partial_name_search(): void
    {
        Symbol::query()->create([
            'asset_type' => 'stock',
            'symbol' => 'AAPL',
            'name' => 'Apple Inc.',
            'market' => 'us_equities',
            'status' => 'active',
            'provider' => 'manual',
            'provider_symbol' => 'AAPL',
        ]);

        $response = $this->getJson('/api/symbols?search=apple');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.symbol', 'AAPL');
    }

    public function test_it_filters_by_asset_type(): void
    {
        Symbol::query()->create([
            'asset_type' => 'stock',
            'symbol' => 'MSFT',
            'name' => 'Microsoft Corporation',
            'market' => 'us_equities',
            'status' => 'active',
            'provider' => 'manual',
            'provider_symbol' => 'MSFT',
        ]);

        Symbol::query()->create([
            'asset_type' => 'etf',
            'symbol' => 'SPY',
            'name' => 'SPDR S&P 500 ETF Trust',
            'market' => 'us_equities',
            'status' => 'active',
            'provider' => 'manual',
            'provider_symbol' => 'SPY',
        ]);

        $response = $this->getJson('/api/symbols?asset_type=etf');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.symbol', 'SPY');
    }

    public function test_it_rejects_invalid_asset_type_filters(): void
    {
        $response = $this->getJson('/api/symbols?asset_type=crypto');

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['asset_type']);
    }
}
