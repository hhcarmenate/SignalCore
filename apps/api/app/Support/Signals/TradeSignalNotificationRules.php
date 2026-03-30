<?php

namespace App\Support\Signals;

use App\Models\TradeSignal;

class TradeSignalNotificationRules
{
    public function classifyPriority(TradeSignal $signal): string
    {
        if ($signal->score >= 85 && $signal->confidence >= 80) {
            return 'high';
        }

        if ($signal->score >= 70 && $signal->confidence >= 65) {
            return 'medium';
        }

        return 'low';
    }

    public function shouldNotify(TradeSignal $signal): bool
    {
        if ($signal->status !== 'new' && $signal->status !== 'accepted') {
            return false;
        }

        if (! in_array($signal->timeframe, ['4h', '1d'], true)) {
            return false;
        }

        if (! in_array($signal->strategy_key, ['trend_continuation', 'breakout_confirmation', 'mean_reversion_to_trend'], true)) {
            return false;
        }

        return in_array($this->classifyPriority($signal), ['high', 'medium'], true);
    }

    public function apply(TradeSignal $signal): TradeSignal
    {
        $priority = $this->classifyPriority($signal);

        $signal->forceFill([
            'notification_priority' => $priority,
            'should_notify' => $this->shouldNotify($signal),
        ])->save();

        return $signal->refresh();
    }
}
