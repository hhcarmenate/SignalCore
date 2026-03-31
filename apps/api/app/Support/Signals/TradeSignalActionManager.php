<?php

namespace App\Support\Signals;

use App\Enums\TradeSignalActionType;
use App\Models\TradeSignal;

class TradeSignalActionManager
{
    public function apply(TradeSignal $signal, TradeSignalActionType $action, ?string $note = null): TradeSignal
    {
        $targetStatus = $action->targetStatus();

        $signal = app(TradeSignalLifecycleManager::class)
            ->transition($signal, $targetStatus, $note);

        $signal->forceFill([
            'last_action' => $action->value,
            'last_action_at' => now(),
            'last_action_note' => $note,
        ])->save();

        if ($action === TradeSignalActionType::QueueForReview) {
            $signal = app(TradeSignalReviewWorkflow::class)->queue($signal, $note);
        }

        return $signal->refresh();
    }
}
