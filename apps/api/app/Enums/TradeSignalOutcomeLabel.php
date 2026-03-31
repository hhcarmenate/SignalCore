<?php

namespace App\Enums;

enum TradeSignalOutcomeLabel: string
{
    case Win = 'win';
    case Loss = 'loss';
    case Neutral = 'neutral';
    case Unresolved = 'unresolved';
}
