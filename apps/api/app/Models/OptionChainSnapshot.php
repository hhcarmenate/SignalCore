<?php

namespace App\Models;

use App\Enums\DataProvider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OptionChainSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'option_contract_id',
        'snapshot_at',
        'bid_price',
        'ask_price',
        'mark_price',
        'last_price',
        'volume',
        'open_interest',
        'implied_volatility',
        'provider',
        'provider_snapshot_id',
        'provider_metadata',
        'is_stale',
    ];

    protected $attributes = [
        'provider' => DataProvider::TwelveData,
        'is_stale' => false,
    ];

    protected $casts = [
        'snapshot_at' => 'immutable_datetime',
        'bid_price' => 'decimal:8',
        'ask_price' => 'decimal:8',
        'mark_price' => 'decimal:8',
        'last_price' => 'decimal:8',
        'volume' => 'integer',
        'open_interest' => 'integer',
        'implied_volatility' => 'decimal:8',
        'provider' => DataProvider::class,
        'provider_metadata' => 'array',
        'is_stale' => 'boolean',
    ];

    public function optionContract(): BelongsTo
    {
        return $this->belongsTo(OptionContract::class);
    }
}
