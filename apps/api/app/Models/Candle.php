<?php

namespace App\Models;

use App\Enums\DataProvider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Candle extends Model
{
    use HasFactory;

    protected $fillable = [
        'symbol_id',
        'timeframe',
        'bar_time',
        'open',
        'high',
        'low',
        'close',
        'volume',
        'provider',
        'vwap',
        'trade_count',
        'session_type',
        'is_final',
    ];

    protected $attributes = [
        'provider' => DataProvider::TwelveData->value,
        'is_final' => true,
    ];

    protected $casts = [
        'bar_time' => 'immutable_datetime',
        'open' => 'decimal:8',
        'high' => 'decimal:8',
        'low' => 'decimal:8',
        'close' => 'decimal:8',
        'vwap' => 'decimal:8',
        'volume' => 'integer',
        'trade_count' => 'integer',
        'is_final' => 'boolean',
    ];

    public function symbol(): BelongsTo
    {
        return $this->belongsTo(Symbol::class);
    }
}
