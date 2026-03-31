<?php

namespace App\Enums;

enum TradeSignalOutcomeState: string
{
    case Pending = 'pending';
    case EntryNotReached = 'entry_not_reached';
    case Entered = 'entered';
    case TargetHit = 'target_hit';
    case StopHit = 'stop_hit';
    case ExpiredAfterEntry = 'expired_after_entry';
    case AmbiguousSameBar = 'ambiguous_same_bar';
    case Cancelled = 'cancelled';
}
