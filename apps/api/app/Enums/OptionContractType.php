<?php

namespace App\Enums;

enum OptionContractType: string
{
    case Call = 'call';
    case Put = 'put';
}
