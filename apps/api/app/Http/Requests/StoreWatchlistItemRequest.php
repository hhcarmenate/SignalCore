<?php

namespace App\Http\Requests;

use App\Models\Watchlist;
use App\Rules\SymbolMatchesWatchlistMarket;
use App\Support\MarketTypeRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWatchlistItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $watchlist = $this->route('watchlist');
        $marketType = $watchlist instanceof Watchlist ? $watchlist->market_type : 'us_equities';

        return [
            'notes' => ['nullable', 'string'],
            'symbol_id' => ['nullable', 'integer', 'exists:symbols,id', new SymbolMatchesWatchlistMarket($marketType)],
            'symbol.asset_type' => ['required_without:symbol_id', 'string', Rule::in(MarketTypeRegistry::allowedAssetTypes($marketType))],
            'symbol.symbol' => ['required_without:symbol_id', 'string', 'max:120'],
            'symbol.name' => ['nullable', 'string', 'max:255'],
            'symbol.exchange' => ['nullable', 'string', 'max:100'],
            'symbol.currency' => ['nullable', 'string', 'max:10'],
            'symbol.provider' => ['nullable', 'string', 'max:50'],
            'symbol.provider_symbol' => ['nullable', 'string', 'max:180'],
        ];
    }
}
