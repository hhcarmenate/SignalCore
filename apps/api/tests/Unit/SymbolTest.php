<?php

namespace Tests\Unit;

use App\Models\Symbol;
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
}
