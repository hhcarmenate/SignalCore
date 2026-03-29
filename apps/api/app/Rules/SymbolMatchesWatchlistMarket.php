<?php

namespace App\Rules;

use App\Models\Symbol;
use App\Support\MarketTypeRegistry;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SymbolMatchesWatchlistMarket implements ValidationRule
{
    public function __construct(private readonly string $marketType)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_numeric($value)) {
            return;
        }

        $symbol = Symbol::query()->find($value);

        if (! $symbol) {
            return;
        }

        if ($symbol->market !== $this->marketType || ! MarketTypeRegistry::isCompatible($this->marketType, $symbol->asset_type)) {
            $fail('The selected symbol is not compatible with the watchlist market.');
        }
    }
}
