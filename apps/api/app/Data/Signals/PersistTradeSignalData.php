<?php

namespace App\Data\Signals;

class PersistTradeSignalData
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public readonly array $attributes,
    ) {}
}
