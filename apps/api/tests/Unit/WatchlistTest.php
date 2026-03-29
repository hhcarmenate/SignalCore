<?php

namespace Tests\Unit;

use App\Models\Symbol;
use App\Models\Watchlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WatchlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_items_relation_returns_latest_items_first(): void
    {
        $watchlist = Watchlist::query()->create([
            'name' => 'Core Momentum',
            'description' => null,
            'market_type' => 'us_equities',
            'is_active' => true,
        ]);

        $firstSymbol = Symbol::query()->create([
            'asset_type' => 'stock',
            'symbol' => 'AMD',
            'name' => 'Advanced Micro Devices',
            'market' => 'us_equities',
            'status' => 'active',
            'provider' => 'manual',
            'provider_symbol' => 'AMD',
        ]);

        $secondSymbol = Symbol::query()->create([
            'asset_type' => 'stock',
            'symbol' => 'AMZN',
            'name' => 'Amazon.com Inc.',
            'market' => 'us_equities',
            'status' => 'active',
            'provider' => 'manual',
            'provider_symbol' => 'AMZN',
        ]);

        $firstItem = $watchlist->items()->create([
            'symbol_id' => $firstSymbol->id,
        ]);

        $secondItem = $watchlist->items()->create([
            'symbol_id' => $secondSymbol->id,
        ]);

        $firstItem->forceFill([
            'created_at' => now()->subMinute(),
            'updated_at' => now()->subMinute(),
        ])->saveQuietly();

        $secondItem->forceFill([
            'created_at' => now(),
            'updated_at' => now(),
        ])->saveQuietly();

        $latestItem = $watchlist->items()->with('symbol')->first();

        $this->assertSame('AMZN', $latestItem?->symbol?->symbol);
    }
}
