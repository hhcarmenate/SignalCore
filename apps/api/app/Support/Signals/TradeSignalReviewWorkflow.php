<?php

namespace App\Support\Signals;

use App\Enums\TradeSignalStatus;
use App\Models\TradeSignal;

class TradeSignalReviewWorkflow
{
    public function queue(TradeSignal $signal, ?string $summary = null): TradeSignal
    {
        return $signal->forceFill([
            'status' => TradeSignalStatus::PendingReview->value,
            'queued_for_review_at' => now(),
            'review_summary' => $summary,
        ])->saveOrFail() ? $signal->refresh() : $signal;
    }

    public function addNote(TradeSignal $signal, string $note): TradeSignal
    {
        $notes = $signal->review_notes ?? [];
        $notes[] = [
            'note' => $note,
            'recorded_at' => now()->toIso8601String(),
        ];

        return $signal->forceFill([
            'review_notes' => $notes,
        ])->saveOrFail() ? $signal->refresh() : $signal;
    }

    public function accept(TradeSignal $signal, string $summary): TradeSignal
    {
        return app(TradeSignalLifecycleManager::class)
            ->transition($signal->forceFill(['review_summary' => $summary]), TradeSignalStatus::Accepted, $summary);
    }

    public function reject(TradeSignal $signal, string $summary): TradeSignal
    {
        return app(TradeSignalLifecycleManager::class)
            ->transition($signal->forceFill(['review_summary' => $summary]), TradeSignalStatus::Rejected, $summary);
    }
}
