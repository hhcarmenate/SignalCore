<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TradeSignalAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'trade_signal_id',
        'event_type',
        'status_before',
        'status_after',
        'action_type',
        'reason',
        'notes',
        'metadata',
        'occurred_at',
    ];

    protected $casts = [
        'notes' => 'array',
        'metadata' => 'array',
        'occurred_at' => 'immutable_datetime',
    ];

    public function tradeSignal(): BelongsTo
    {
        return $this->belongsTo(TradeSignal::class);
    }
}
