<?php

namespace App\Models;

use App\Enums\TradeSignalStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TradeSignal extends Model
{
    use HasFactory;

    protected $fillable = [
        'watchlist_id',
        'symbol_id',
        'scanner_strategy_id',
        'strategy_key',
        'timeframe',
        'direction',
        'execution_hint',
        'signal_category',
        'status',
        'entry_price',
        'stop_loss',
        'target_price',
        'score',
        'confidence',
        'ranking_score',
        'ranking_position',
        'thesis',
        'score_breakdown',
        'indicator_snapshot',
        'market_context',
        'metadata',
        'signal_generated_at',
        'expires_at',
        'reviewed_at',
        'invalidated_at',
        'actioned_at',
        'status_reason',
        'queued_for_review_at',
        'review_summary',
        'review_notes',
        'notification_priority',
        'should_notify',
        'notified_at',
        'fingerprint',
        'setup_key',
        'bar_time',
        'is_duplicate',
        'replaces_trade_signal_id',
    ];

    protected $casts = [
        'entry_price' => 'decimal:8',
        'stop_loss' => 'decimal:8',
        'target_price' => 'decimal:8',
        'score' => 'decimal:2',
        'confidence' => 'decimal:2',
        'ranking_score' => 'decimal:2',
        'ranking_position' => 'integer',
        'score_breakdown' => 'array',
        'indicator_snapshot' => 'array',
        'market_context' => 'array',
        'metadata' => 'array',
        'review_notes' => 'array',
        'signal_generated_at' => 'immutable_datetime',
        'expires_at' => 'immutable_datetime',
        'reviewed_at' => 'immutable_datetime',
        'invalidated_at' => 'immutable_datetime',
        'actioned_at' => 'immutable_datetime',
        'queued_for_review_at' => 'immutable_datetime',
        'notified_at' => 'immutable_datetime',
        'bar_time' => 'immutable_datetime',
        'should_notify' => 'boolean',
        'is_duplicate' => 'boolean',
    ];

    protected $attributes = [
        'status' => TradeSignalStatus::New->value,
        'should_notify' => false,
        'is_duplicate' => false,
    ];

    public function watchlist(): BelongsTo
    {
        return $this->belongsTo(Watchlist::class);
    }

    public function symbol(): BelongsTo
    {
        return $this->belongsTo(Symbol::class);
    }

    public function scannerStrategy(): BelongsTo
    {
        return $this->belongsTo(ScannerStrategy::class);
    }

    public function replacedSignal(): BelongsTo
    {
        return $this->belongsTo(self::class, 'replaces_trade_signal_id');
    }

    public function replacements(): HasMany
    {
        return $this->hasMany(self::class, 'replaces_trade_signal_id');
    }
}
