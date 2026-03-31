<?php

namespace Tests\Feature;

use App\Enums\OptionContractType;
use App\Models\OptionChainSnapshot;
use App\Models\OptionContract;
use App\Models\Symbol;
use App\Models\TradeSignal;
use App\Support\Options\OptionContractSuggestionEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OptionContractSuggestionEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_maps_bullish_signals_to_call_contract_suggestions(): void
    {
        $signal = $this->makeSignal('bullish');
        $preferred = $this->makeContract($signal->symbol_id, 'AAPL_20260515_C_220', OptionContractType::Call, 220, '2026-05-15');
        $alternate = $this->makeContract($signal->symbol_id, 'AAPL_20260619_C_225', OptionContractType::Call, 225, '2026-06-19');
        $this->makeContract($signal->symbol_id, 'AAPL_20260515_P_220', OptionContractType::Put, 220, '2026-05-15');

        $this->makeSnapshot($preferred, 2.10, 2.25, 1600, 8000, 0.32);
        $this->makeSnapshot($alternate, 2.00, 2.30, 1400, 7000, 0.31);

        $result = app(OptionContractSuggestionEngine::class)->suggest($signal, 218);

        $this->assertSame('call', $result['preferred_option_type']);
        $this->assertNotNull($result['primary_suggestion']);
        $this->assertSame('AAPL_20260515_C_220', $result['primary_suggestion']['contract_symbol']);
        $this->assertSame('call', $result['primary_suggestion']['option_type']);
        $this->assertNotEmpty($result['alternate_suggestions']);
    }

    public function test_it_maps_bearish_signals_to_put_contract_suggestions(): void
    {
        $signal = $this->makeSignal('bearish', 'TSLA');
        $put = $this->makeContract($signal->symbol_id, 'TSLA_20260515_P_180', OptionContractType::Put, 180, '2026-05-15');
        $call = $this->makeContract($signal->symbol_id, 'TSLA_20260515_C_180', OptionContractType::Call, 180, '2026-05-15');

        $this->makeSnapshot($put, 3.10, 3.25, 1800, 9000, 0.41);
        $this->makeSnapshot($call, 2.50, 2.90, 1800, 9000, 0.37);

        $result = app(OptionContractSuggestionEngine::class)->suggest($signal, 184);

        $this->assertSame('put', $result['preferred_option_type']);
        $this->assertSame('TSLA_20260515_P_180', $result['primary_suggestion']['contract_symbol']);
    }

    public function test_it_respects_liquidity_filters_and_returns_no_primary_candidate_when_none_pass(): void
    {
        $signal = $this->makeSignal('bullish', 'MSFT');
        $illiquid = $this->makeContract($signal->symbol_id, 'MSFT_20260515_C_400', OptionContractType::Call, 400, '2026-05-15');
        $this->makeSnapshot($illiquid, null, null, 5, 20, 0.29);

        $result = app(OptionContractSuggestionEngine::class)->suggest($signal, 398);

        $this->assertNull($result['primary_suggestion']);
        $this->assertSame(0, $result['candidate_count']);
    }

    private function makeSignal(string $direction, string $symbol = 'AAPL'): TradeSignal
    {
        $underlying = Symbol::query()->create([
            'asset_type' => 'stock',
            'symbol' => $symbol,
            'name' => $symbol.' Underlying',
            'market' => 'us_equities',
            'provider' => 'twelve_data',
            'provider_symbol' => $symbol,
        ]);

        return TradeSignal::query()->create([
            'symbol_id' => $underlying->id,
            'strategy_key' => 'trend_continuation',
            'timeframe' => '4h',
            'direction' => $direction,
            'execution_hint' => $direction === 'bullish' ? 'call' : 'put',
            'signal_category' => 'trend_continuation',
            'entry_price' => 100,
            'stop_loss' => 95,
            'target_price' => 110,
            'thesis' => 'Options suggestion test signal.',
            'signal_generated_at' => '2026-03-31T12:00:00Z',
        ]);
    }

    private function makeContract(int $underlyingSymbolId, string $contractSymbol, OptionContractType $type, float $strike, string $expiration): OptionContract
    {
        return OptionContract::query()->create([
            'underlying_symbol_id' => $underlyingSymbolId,
            'contract_symbol' => $contractSymbol,
            'provider_contract_symbol' => str_replace(['_', '.'], '', $contractSymbol),
            'option_type' => $type,
            'strike_price' => $strike,
            'expiration_date' => $expiration,
            'provider' => 'twelve_data',
        ]);
    }

    private function makeSnapshot(OptionContract $contract, ?float $bid, ?float $ask, int $volume, int $openInterest, float $iv): OptionChainSnapshot
    {
        return OptionChainSnapshot::query()->create([
            'option_contract_id' => $contract->id,
            'snapshot_at' => '2026-03-31T17:15:00Z',
            'bid_price' => $bid,
            'ask_price' => $ask,
            'mark_price' => ($bid !== null && $ask !== null) ? (($bid + $ask) / 2) : null,
            'volume' => $volume,
            'open_interest' => $openInterest,
            'implied_volatility' => $iv,
            'provider' => 'twelve_data',
        ]);
    }
}
