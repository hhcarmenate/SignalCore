<?php

namespace Database\Seeders;

use App\Models\Symbol;
use Illuminate\Database\Seeder;

class SymbolSeeder extends Seeder
{
    public function run(): void
    {
        $symbols = [
            ['asset_type' => 'stock', 'symbol' => 'AAPL', 'name' => 'Apple Inc.', 'exchange' => 'NASDAQ'],
            ['asset_type' => 'stock', 'symbol' => 'AMD', 'name' => 'Advanced Micro Devices, Inc.', 'exchange' => 'NASDAQ'],
            ['asset_type' => 'stock', 'symbol' => 'AMZN', 'name' => 'Amazon.com, Inc.', 'exchange' => 'NASDAQ'],
            ['asset_type' => 'stock', 'symbol' => 'AVGO', 'name' => 'Broadcom Inc.', 'exchange' => 'NASDAQ'],
            ['asset_type' => 'etf', 'symbol' => 'DIA', 'name' => 'SPDR Dow Jones Industrial Average ETF Trust', 'exchange' => 'NYSE Arca'],
            ['asset_type' => 'stock', 'symbol' => 'GOOG', 'name' => 'Alphabet Inc. Class C', 'exchange' => 'NASDAQ'],
            ['asset_type' => 'etf', 'symbol' => 'IWM', 'name' => 'iShares Russell 2000 ETF', 'exchange' => 'NYSE Arca'],
            ['asset_type' => 'stock', 'symbol' => 'META', 'name' => 'Meta Platforms, Inc.', 'exchange' => 'NASDAQ'],
            ['asset_type' => 'stock', 'symbol' => 'MSFT', 'name' => 'Microsoft Corporation', 'exchange' => 'NASDAQ'],
            ['asset_type' => 'stock', 'symbol' => 'MU', 'name' => 'Micron Technology, Inc.', 'exchange' => 'NASDAQ'],
            ['asset_type' => 'stock', 'symbol' => 'NFLX', 'name' => 'Netflix, Inc.', 'exchange' => 'NASDAQ'],
            ['asset_type' => 'stock', 'symbol' => 'NVDA', 'name' => 'NVIDIA Corporation', 'exchange' => 'NASDAQ'],
            ['asset_type' => 'stock', 'symbol' => 'QCOM', 'name' => 'QUALCOMM Incorporated', 'exchange' => 'NASDAQ'],
            ['asset_type' => 'etf', 'symbol' => 'QQQ', 'name' => 'Invesco QQQ Trust', 'exchange' => 'NASDAQ'],
            ['asset_type' => 'etf', 'symbol' => 'SPY', 'name' => 'SPDR S&P 500 ETF Trust', 'exchange' => 'NYSE Arca'],
            ['asset_type' => 'stock', 'symbol' => 'TSLA', 'name' => 'Tesla, Inc.', 'exchange' => 'NASDAQ'],
        ];

        foreach ($symbols as $symbol) {
            Symbol::query()->updateOrCreate(
                [
                    'market' => 'us_equities',
                    'symbol' => $symbol['symbol'],
                ],
                [
                    'asset_type' => $symbol['asset_type'],
                    'name' => $symbol['name'],
                    'exchange' => $symbol['exchange'],
                    'status' => 'active',
                    'currency' => 'USD',
                    'provider' => 'manual',
                    'provider_symbol' => $symbol['symbol'],
                    'metadata' => null,
                ],
            );
        }
    }
}
