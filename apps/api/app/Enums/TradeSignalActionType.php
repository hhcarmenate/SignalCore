<?php

namespace App\Enums;

enum TradeSignalActionType: string
{
    case QueueForReview = 'queue_for_review';
    case Accept = 'accept';
    case Reject = 'reject';
    case Ignore = 'ignore';
    case MarkActioned = 'mark_actioned';
    case Expire = 'expire';
    case Invalidate = 'invalidate';

    public function targetStatus(): TradeSignalStatus
    {
        return match ($this) {
            self::QueueForReview => TradeSignalStatus::PendingReview,
            self::Accept => TradeSignalStatus::Accepted,
            self::Reject => TradeSignalStatus::Rejected,
            self::Ignore, self::Invalidate => TradeSignalStatus::Ignored,
            self::MarkActioned => TradeSignalStatus::Actioned,
            self::Expire => TradeSignalStatus::Expired,
        };
    }

    public function isManual(): bool
    {
        return match ($this) {
            self::QueueForReview, self::Accept, self::Reject, self::Ignore, self::MarkActioned => true,
            self::Expire, self::Invalidate => false,
        };
    }
}
