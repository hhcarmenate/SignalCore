<?php

namespace Tests\Feature;

use App\Enums\DataProvider;
use App\Enums\Market;
use App\Models\Symbol;
use Database\Seeders\SymbolSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SymbolSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_the_approved_initial_symbol_universe(): void
    {
        $this->seed(SymbolSeeder::class);

        $seededSymbols = Symbol::query()
            ->orderBy('symbol')
            ->pluck('symbol')
            ->all();

        $this->assertSame([
            'AAPL',
            'AMD',
            'AMZN',
            'AVGO',
            'DIA',
            'GOOG',
            'IWM',
            'META',
            'MSFT',
            'MU',
            'NFLX',
            'NVDA',
            'PLTR',
            'QCOM',
            'QQQ',
            'SPY',
            'TSLA',
        ], $seededSymbols);

        $pltr = Symbol::query()->where('symbol', 'PLTR')->firstOrFail();

        $this->assertSame(Market::UsEquities->value, $pltr->market);
        $this->assertSame(DataProvider::TwelveData->value, $pltr->provider);
        $this->assertSame('PLTR', $pltr->provider_symbol);
        $this->assertSame('active', $pltr->status);
        $this->assertSame('USD', $pltr->currency);
    }

    public function test_it_is_idempotent_when_reseeding_the_universe(): void
    {
        $this->seed(SymbolSeeder::class);
        $this->seed(SymbolSeeder::class);

        $this->assertSame(count(SymbolSeeder::universe()), Symbol::query()->count());
    }
}
