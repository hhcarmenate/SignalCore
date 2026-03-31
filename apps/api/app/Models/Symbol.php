<?php

namespace App\Models;

use App\Enums\DataProvider;
use App\Enums\Market;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Symbol extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_type',
        'symbol',
        'name',
        'market',
        'exchange',
        'status',
        'currency',
        'provider',
        'provider_symbol',
        'metadata',
    ];

    protected $attributes = [
        'market' => Market::UsEquities->value,
        'status' => 'active',
        'currency' => 'USD',
        'provider' => DataProvider::TwelveData->value,
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (Symbol $symbol): void {
            $symbol->symbol = Str::upper(trim((string) $symbol->symbol));
            $symbol->provider_symbol = $symbol->provider_symbol !== null
                ? Str::upper(trim((string) $symbol->provider_symbol))
                : $symbol->symbol;
        });
    }

    public function watchlistItems(): HasMany
    {
        return $this->hasMany(WatchlistItem::class);
    }

    public function optionContracts(): HasMany
    {
        return $this->hasMany(OptionContract::class, 'underlying_symbol_id');
    }
}
