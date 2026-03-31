<?php

namespace App\Support\Signals;

use App\Models\TradeSignal;
use App\Models\TradeSignalAudit;

class TradeSignalAuditLogger
{
    /**
     * @param  array<string, mixed>  $metadata
     * @param  array<int, array<string, mixed>>  $notes
     */
    public function log(
        TradeSignal $signal,
        string $eventType,
        ?string $statusBefore = null,
        ?string $statusAfter = null,
        ?string $actionType = null,
        ?string $reason = null,
        array $notes = [],
        array $metadata = [],
    ): TradeSignalAudit {
        return TradeSignalAudit::query()->create([
            'trade_signal_id' => $signal->id,
            'event_type' => $eventType,
            'status_before' => $statusBefore,
            'status_after' => $statusAfter,
            'action_type' => $actionType,
            'reason' => $reason,
            'notes' => $notes,
            'metadata' => $metadata,
            'occurred_at' => now(),
        ]);
    }
}
