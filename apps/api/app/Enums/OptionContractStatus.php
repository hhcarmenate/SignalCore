<?php

namespace App\Enums;

enum OptionContractStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case Delisted = 'delisted';
    case Inactive = 'inactive';
}
