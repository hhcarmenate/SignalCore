<?php

namespace App\Models;

use App\Enums\DataProvider;
use App\Enums\OptionContractStatus;
use App\Enums\OptionContractType;
use App\Enums\OptionExerciseStyle;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OptionContract extends Model
{
    use HasFactory;

    protected $fillable = [
        'underlying_symbol_id',
        'contract_symbol',
        'provider_contract_symbol',
        'option_type',
        'strike_price',
        'expiration_date',
        'is_active',
        'status',
        'multiplier',
        'exercise_style',
        'shares_per_contract',
        'provider',
        'provider_metadata',
        'listed_at',
        'delisted_at',
    ];

    protected $attributes = [
        'is_active' => true,
        'status' => OptionContractStatus::Active,
        'multiplier' => 100,
        'shares_per_contract' => 100,
        'provider' => DataProvider::TwelveData,
        'exercise_style' => OptionExerciseStyle::American,
    ];

    protected $casts = [
        'option_type' => OptionContractType::class,
        'status' => OptionContractStatus::class,
        'exercise_style' => OptionExerciseStyle::class,
        'provider' => DataProvider::class,
        'strike_price' => 'decimal:8',
        'expiration_date' => 'date',
        'is_active' => 'boolean',
        'multiplier' => 'integer',
        'shares_per_contract' => 'integer',
        'provider_metadata' => 'array',
        'listed_at' => 'immutable_datetime',
        'delisted_at' => 'immutable_datetime',
    ];

    public function underlyingSymbol(): BelongsTo
    {
        return $this->belongsTo(Symbol::class, 'underlying_symbol_id');
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(OptionChainSnapshot::class);
    }
}
