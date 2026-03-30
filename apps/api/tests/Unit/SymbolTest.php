<?php

namespace Tests\Unit;

use App\Enums\DataProvider;
use App\Enums\Market;
use App\Models\Symbol;
use App\Models\WatchlistItem;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SymbolTest extends TestCase
{
    use RefreshDatabase;

    public function test_metadata_is_cast_to_array(): void
    {
        $symbol = Symbol::query()->create([
            'asset_type' => 'stock',
            'symbol' => 'NVDA',
            'name' => 'NVIDIA Corporation',
            'market' => 'us_equities',
            'status' => 'active',
            'provider' => 'manual',
            'provider_symbol' => 'NVDA',
            'metadata' => [
                'sector' => 'Technology',
                'industry' => 'Semiconductors',
            ],
        ]);

        $this->assertIsArray($symbol->metadata);
        $this->assertSame('Technology', $symbol->metadata['sector']);
    }

    public function test_it_normalizes_symbol_fields_before_saving(): void
    {
        $symbol = Symbol::query()->create([
            'asset_type' => 'etf',
            'symbol' => ' spy ',
            'name' => 'SPDR S&P 500 ETF Trust',
            'market' => 'us_equities',
            'status' => 'active',
            'provider' => 'manual',
            'provider_symbol' => ' spy ',
        ]);

        $this->assertSame('SPY', $symbol->symbol);
        $this->assertSame('SPY', $symbol->provider_symbol);
    }

    public function test_it_applies_model_defaults_and_uses_the_symbol_as_provider_symbol(): void
    {
        $symbol = Symbol::query()->create([
            'asset_type' => 'stock',
            'symbol' => 'aapl',
            'name' => 'Apple Inc.',
        ]);

        $this->assertSame(Market::UsEquities->value, $symbol->market);
        $this->assertSame('active', $symbol->status);
        $this->assertSame('USD', $symbol->currency);
        $this->assertSame(DataProvider::TwelveData->value, $symbol->provider);
        $this->assertSame('AAPL', $symbol->provider_symbol);
    }

    public function test_it_exposes_the_watchlist_items_relationship(): void
    {
        $relation = (new Symbol)->watchlistItems();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertInstanceOf(WatchlistItem::class, $relation->getRelated());
    }
}
