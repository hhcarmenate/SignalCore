<?php

namespace App\Models;

use App\Enums\TradeSignalOutcomeLabel;
use App\Enums\TradeSignalOutcomeState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TradeSignalOutcome extends Model
{
    use HasFactory;

    protected $fillable = [
        'trade_signal_id',
        'evaluation_state',
        'outcome_label',
        'entry_reached',
        'entry_reached_at',
        'target_hit',
        'target_hit_at',
        'stop_hit',
        'stop_hit_at',
        'expired_without_entry',
        'expired_after_entry',
        'evaluation_started_at',
        'evaluation_completed_at',
        'expired_at',
        'evaluation_assumption_key',
        'ambiguity_reason',
        'notes',
        'max_favorable_excursion',
        'max_adverse_excursion',
        'price_after_1d',
        'price_after_3d',
        'price_after_5d',
    ];

    protected $casts = [
        'evaluation_state' => TradeSignalOutcomeState::class,
        'outcome_label' => TradeSignalOutcomeLabel::class,
        'entry_reached' => 'boolean',
        'target_hit' => 'boolean',
        'stop_hit' => 'boolean',
        'expired_without_entry' => 'boolean',
        'expired_after_entry' => 'boolean',
        'entry_reached_at' => 'immutable_datetime',
        'target_hit_at' => 'immutable_datetime',
        'stop_hit_at' => 'immutable_datetime',
        'evaluation_started_at' => 'immutable_datetime',
        'evaluation_completed_at' => 'immutable_datetime',
        'expired_at' => 'immutable_datetime',
        'max_favorable_excursion' => 'decimal:8',
        'max_adverse_excursion' => 'decimal:8',
        'price_after_1d' => 'decimal:8',
        'price_after_3d' => 'decimal:8',
        'price_after_5d' => 'decimal:8',
    ];

    protected $attributes = [
        'evaluation_state' => TradeSignalOutcomeState::Pending,
        'outcome_label' => TradeSignalOutcomeLabel::Unresolved,
        'entry_reached' => false,
        'target_hit' => false,
        'stop_hit' => false,
        'expired_without_entry' => false,
        'expired_after_entry' => false,
    ];

    public function tradeSignal(): BelongsTo
    {
        return $this->belongsTo(TradeSignal::class);
    }
}
