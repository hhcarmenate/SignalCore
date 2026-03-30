<?php

namespace App\Support\Signals;

use App\Enums\TradeSignalStatus;
use App\Models\TradeSignal;
use InvalidArgumentException;

class TradeSignalLifecycleManager
{
    public function transition(TradeSignal $signal, TradeSignalStatus $targetStatus, ?string $reason = null): TradeSignal
    {
        $currentStatus = TradeSignalStatus::from($signal->status);

        if (! $currentStatus->canTransitionTo($targetStatus)) {
            throw new InvalidArgumentException(sprintf(
                'Cannot transition trade signal from [%s] to [%s].',
                $currentStatus->value,
                $targetStatus->value,
            ));
        }

        $attributes = [
            'status' => $targetStatus->value,
            'status_reason' => $reason,
        ];

        if (in_array($targetStatus, [TradeSignalStatus::Accepted, TradeSignalStatus::Rejected], true)) {
            $attributes['reviewed_at'] = now();
        }

        if (in_array($targetStatus, [TradeSignalStatus::Expired, TradeSignalStatus::Ignored], true)) {
            $attributes['invalidated_at'] = now();
        }

        if ($targetStatus === TradeSignalStatus::Actioned) {
            $attributes['actioned_at'] = now();
        }

        $signal->forceFill($attributes)->save();

        return $signal->refresh();
    }
}
