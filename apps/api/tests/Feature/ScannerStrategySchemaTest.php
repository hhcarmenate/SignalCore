<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ScannerStrategySchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_persists_scanner_strategies_with_global_enablement_state(): void
    {
        $strategyId = DB::table('scanner_strategies')->insertGetId([
            'key' => 'trend_continuation',
            'name' => 'Trend Continuation',
            'description' => 'Follow continuation setups in the prevailing trend.',
            'is_enabled' => true,
            'is_active' => true,
            'sort_order' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $strategy = DB::table('scanner_strategies')->where('id', $strategyId)->first();

        $this->assertNotNull($strategy);
        $this->assertSame('trend_continuation', $strategy->key);
        $this->assertTrue((bool) $strategy->is_enabled);
        $this->assertTrue((bool) $strategy->is_active);
        $this->assertSame(10, $strategy->sort_order);
    }

    public function test_it_supports_watchlist_to_strategy_assignment_with_per_watchlist_enablement(): void
    {
        $watchlistId = DB::table('watchlists')->insertGetId([
            'name' => 'Momentum Watchlist',
            'description' => 'Scanner execution watchlist',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $strategyId = DB::table('scanner_strategies')->insertGetId([
            'key' => 'breakout_confirmation',
            'name' => 'Breakout Confirmation',
            'description' => null,
            'is_enabled' => true,
            'is_active' => true,
            'sort_order' => 20,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('watchlist_scanner_strategy')->insert([
            'watchlist_id' => $watchlistId,
            'scanner_strategy_id' => $strategyId,
            'is_enabled' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $assignment = DB::table('watchlist_scanner_strategy')
            ->where('watchlist_id', $watchlistId)
            ->where('scanner_strategy_id', $strategyId)
            ->first();

        $this->assertNotNull($assignment);
        $this->assertFalse((bool) $assignment->is_enabled);
    }

    public function test_it_prevents_duplicate_watchlist_strategy_assignments(): void
    {
        $watchlistId = DB::table('watchlists')->insertGetId([
            'name' => 'Trend Watchlist',
            'description' => null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $strategyId = DB::table('scanner_strategies')->insertGetId([
            'key' => 'mean_reversion_to_trend',
            'name' => 'Mean Reversion to Trend',
            'description' => null,
            'is_enabled' => true,
            'is_active' => true,
            'sort_order' => 30,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('watchlist_scanner_strategy')->insert([
            'watchlist_id' => $watchlistId,
            'scanner_strategy_id' => $strategyId,
            'is_enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(QueryException::class);

        DB::table('watchlist_scanner_strategy')->insert([
            'watchlist_id' => $watchlistId,
            'scanner_strategy_id' => $strategyId,
            'is_enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
