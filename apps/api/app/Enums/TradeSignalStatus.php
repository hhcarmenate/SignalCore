<?php

namespace App\Enums;

enum TradeSignalStatus: string
{
    case New = 'new';
    case PendingReview = 'pending_review';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Expired = 'expired';
    case Actioned = 'actioned';
    case Ignored = 'ignored';

    /**
     * @return array<string>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::New => [self::PendingReview->value, self::Accepted->value, self::Rejected->value, self::Expired->value, self::Ignored->value],
            self::PendingReview => [self::Accepted->value, self::Rejected->value, self::Expired->value, self::Ignored->value],
            self::Accepted => [self::Actioned->value, self::Expired->value],
            self::Rejected => [],
            self::Expired => [],
            self::Actioned => [],
            self::Ignored => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target->value, $this->allowedTransitions(), true);
    }
}
