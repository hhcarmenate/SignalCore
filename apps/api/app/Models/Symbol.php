<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    protected $casts = [
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (Symbol $symbol): void {
            $symbol->symbol = strtoupper(trim((string) $symbol->symbol));

            if ($symbol->provider_symbol !== null) {
                $symbol->provider_symbol = strtoupper(trim((string) $symbol->provider_symbol));
            }
        });
    }

    public function watchlistItems(): HasMany
    {
        return $this->hasMany(WatchlistItem::class);
    }
}
