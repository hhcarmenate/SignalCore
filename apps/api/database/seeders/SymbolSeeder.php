<?php

namespace Database\Seeders;

use App\Enums\AssetType;
use App\Enums\DataProvider;
use App\Enums\Market;
use App\Models\Symbol;
use Illuminate\Database\Seeder;

class SymbolSeeder extends Seeder
{
    /**
     * @return array<int, array<string, string>>
     */
    public static function universe(): array
    {
        return [
            ['asset_type' => AssetType::Stock->value, 'symbol' => 'AAPL', 'name' => 'Apple Inc.', 'exchange' => 'NASDAQ'],
            ['asset_type' => AssetType::Stock->value, 'symbol' => 'AMD', 'name' => 'Advanced Micro Devices, Inc.', 'exchange' => 'NASDAQ'],
            ['asset_type' => AssetType::Stock->value, 'symbol' => 'AMZN', 'name' => 'Amazon.com, Inc.', 'exchange' => 'NASDAQ'],
            ['asset_type' => AssetType::Stock->value, 'symbol' => 'AVGO', 'name' => 'Broadcom Inc.', 'exchange' => 'NASDAQ'],
            ['asset_type' => AssetType::Etf->value, 'symbol' => 'DIA', 'name' => 'SPDR Dow Jones Industrial Average ETF Trust', 'exchange' => 'NYSE Arca'],
            ['asset_type' => AssetType::Stock->value, 'symbol' => 'GOOG', 'name' => 'Alphabet Inc. Class C', 'exchange' => 'NASDAQ'],
            ['asset_type' => AssetType::Etf->value, 'symbol' => 'IWM', 'name' => 'iShares Russell 2000 ETF', 'exchange' => 'NYSE Arca'],
            ['asset_type' => AssetType::Stock->value, 'symbol' => 'META', 'name' => 'Meta Platforms, Inc.', 'exchange' => 'NASDAQ'],
            ['asset_type' => AssetType::Stock->value, 'symbol' => 'MSFT', 'name' => 'Microsoft Corporation', 'exchange' => 'NASDAQ'],
            ['asset_type' => AssetType::Stock->value, 'symbol' => 'MU', 'name' => 'Micron Technology, Inc.', 'exchange' => 'NASDAQ'],
            ['asset_type' => AssetType::Stock->value, 'symbol' => 'NFLX', 'name' => 'Netflix, Inc.', 'exchange' => 'NASDAQ'],
            ['asset_type' => AssetType::Stock->value, 'symbol' => 'NVDA', 'name' => 'NVIDIA Corporation', 'exchange' => 'NASDAQ'],
            ['asset_type' => AssetType::Stock->value, 'symbol' => 'PLTR', 'name' => 'Palantir Technologies Inc.', 'exchange' => 'NASDAQ'],
            ['asset_type' => AssetType::Stock->value, 'symbol' => 'QCOM', 'name' => 'QUALCOMM Incorporated', 'exchange' => 'NASDAQ'],
            ['asset_type' => AssetType::Etf->value, 'symbol' => 'QQQ', 'name' => 'Invesco QQQ Trust', 'exchange' => 'NASDAQ'],
            ['asset_type' => AssetType::Etf->value, 'symbol' => 'SPY', 'name' => 'SPDR S&P 500 ETF Trust', 'exchange' => 'NYSE Arca'],
            ['asset_type' => AssetType::Stock->value, 'symbol' => 'TSLA', 'name' => 'Tesla, Inc.', 'exchange' => 'NASDAQ'],
        ];
    }

    public function run(): void
    {
        foreach (self::universe() as $symbol) {
            Symbol::query()->updateOrCreate(
                [
                    'market' => Market::UsEquities->value,
                    'symbol' => $symbol['symbol'],
                ],
                [
                    'asset_type' => $symbol['asset_type'],
                    'name' => $symbol['name'],
                    'exchange' => $symbol['exchange'],
                    'status' => 'active',
                    'currency' => 'USD',
                    'provider' => DataProvider::TwelveData->value,
                    'provider_symbol' => $symbol['symbol'],
                    'metadata' => null,
                ],
            );
        }
    }
}
