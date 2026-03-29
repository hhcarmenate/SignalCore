<?php

namespace Tests\Feature;

use App\Models\Symbol;
use App\Models\Watchlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WatchlistApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_watchlists(): void
    {
        $watchlist = Watchlist::query()->create([
            'name' => 'Core Momentum',
            'description' => 'Primary discretionary names',
            'market_type' => 'us_equities',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/watchlists');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $watchlist->id)
            ->assertJsonPath('data.0.name', 'Core Momentum')
            ->assertJsonPath('data.0.market_type', 'us_equities')
            ->assertJsonPath('data.0.items_count', 0);
    }

    public function test_it_creates_a_watchlist(): void
    {
        $response = $this->postJson('/api/watchlists', [
            'name' => 'Swing Setup Board',
            'description' => 'High priority names for review',
            'market_type' => 'us_equities',
            'is_active' => true,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'Swing Setup Board')
            ->assertJsonPath('data.description', 'High priority names for review')
            ->assertJsonPath('data.market_type', 'us_equities')
            ->assertJsonPath('data.is_active', true);

        $this->assertDatabaseHas('watchlists', [
            'name' => 'Swing Setup Board',
            'market_type' => 'us_equities',
        ]);
    }

    public function test_it_shows_a_watchlist_with_items(): void
    {
        $watchlist = Watchlist::query()->create([
            'name' => 'Earnings Watch',
            'description' => null,
            'market_type' => 'us_equities',
            'is_active' => true,
        ]);

        $symbol = Symbol::query()->create([
            'asset_type' => 'stock',
            'symbol' => 'NVDA',
            'name' => 'NVIDIA Corp',
            'market' => 'us_equities',
            'status' => 'active',
            'provider' => 'manual',
            'provider_symbol' => 'NVDA',
        ]);

        $item = $watchlist->items()->create([
            'symbol_id' => $symbol->id,
            'notes' => 'Watch for continuation setup',
        ]);

        $response = $this->getJson("/api/watchlists/{$watchlist->id}");

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $watchlist->id)
            ->assertJsonPath('data.market_type', 'us_equities')
            ->assertJsonPath('data.items.0.id', $item->id)
            ->assertJsonPath('data.items.0.symbol.symbol', 'NVDA');
    }

    public function test_it_creates_a_manual_symbol_when_adding_a_watchlist_item(): void
    {
        $watchlist = Watchlist::query()->create([
            'name' => 'Core Momentum',
            'description' => null,
            'market_type' => 'us_equities',
            'is_active' => true,
        ]);

        $payload = [
            'notes' => 'High conviction AI name',
            'symbol' => [
                'asset_type' => 'stock',
                'symbol' => 'AAPL',
                'name' => 'Apple Inc.',
                'exchange' => 'NASDAQ',
                'currency' => 'USD',
            ],
        ];

        $response = $this->postJson("/api/watchlists/{$watchlist->id}/items", $payload);

        $response
            ->assertCreated()
            ->assertJsonPath('data.notes', 'High conviction AI name')
            ->assertJsonPath('data.symbol.symbol', 'AAPL')
            ->assertJsonPath('data.symbol.market', 'us_equities')
            ->assertJsonPath('data.symbol.provider', 'manual')
            ->assertJsonPath('data.symbol.status', 'active');

        $this->assertDatabaseHas('symbols', [
            'symbol' => 'AAPL',
            'market' => 'us_equities',
            'provider' => 'manual',
            'provider_symbol' => 'AAPL',
        ]);

        $this->assertDatabaseHas('watchlist_items', [
            'watchlist_id' => $watchlist->id,
            'notes' => 'High conviction AI name',
        ]);
    }

    public function test_it_rejects_incompatible_asset_types_for_a_market_specific_watchlist(): void
    {
        $watchlist = Watchlist::query()->create([
            'name' => 'Crypto Momentum',
            'description' => null,
            'market_type' => 'crypto',
            'is_active' => true,
        ]);

        $response = $this->postJson("/api/watchlists/{$watchlist->id}/items", [
            'symbol' => [
                'asset_type' => 'stock',
                'symbol' => 'AAPL',
                'name' => 'Apple Inc.',
            ],
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['symbol.asset_type']);
    }

    public function test_it_rejects_duplicate_symbols_in_the_same_watchlist(): void
    {
        $watchlist = Watchlist::query()->create([
            'name' => 'Core Momentum',
            'description' => null,
            'market_type' => 'us_equities',
            'is_active' => true,
        ]);

        $symbol = Symbol::query()->create([
            'asset_type' => 'stock',
            'symbol' => 'MSFT',
            'name' => 'Microsoft Corporation',
            'market' => 'us_equities',
            'status' => 'active',
            'provider' => 'manual',
            'provider_symbol' => 'MSFT',
        ]);

        $watchlist->items()->create([
            'symbol_id' => $symbol->id,
            'notes' => null,
        ]);

        $response = $this->postJson("/api/watchlists/{$watchlist->id}/items", [
            'symbol_id' => $symbol->id,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['symbol']);
    }

    public function test_it_deletes_a_watchlist_item(): void
    {
        $watchlist = Watchlist::query()->create([
            'name' => 'Delete Item Test',
            'description' => null,
            'market_type' => 'us_equities',
            'is_active' => true,
        ]);

        $symbol = Symbol::query()->create([
            'asset_type' => 'stock',
            'symbol' => 'QQQ',
            'name' => 'Invesco QQQ Trust',
            'market' => 'us_equities',
            'status' => 'active',
            'provider' => 'manual',
            'provider_symbol' => 'QQQ',
        ]);

        $item = $watchlist->items()->create([
            'symbol_id' => $symbol->id,
            'notes' => 'Remove me',
        ]);

        $response = $this->deleteJson("/api/watchlists/{$watchlist->id}/items/{$item->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('watchlist_items', [
            'id' => $item->id,
        ]);
    }

    public function test_it_deletes_a_watchlist_and_cascades_items(): void
    {
        $watchlist = Watchlist::query()->create([
            'name' => 'Disposable List',
            'description' => null,
            'market_type' => 'us_equities',
            'is_active' => true,
        ]);

        $symbol = Symbol::query()->create([
            'asset_type' => 'stock',
            'symbol' => 'TSLA',
            'name' => 'Tesla Inc.',
            'market' => 'us_equities',
            'status' => 'active',
            'provider' => 'manual',
            'provider_symbol' => 'TSLA',
        ]);

        $item = $watchlist->items()->create([
            'symbol_id' => $symbol->id,
            'notes' => null,
        ]);

        $response = $this->deleteJson("/api/watchlists/{$watchlist->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('watchlists', [
            'id' => $watchlist->id,
        ]);

        $this->assertDatabaseMissing('watchlist_items', [
            'id' => $item->id,
        ]);
    }
}
