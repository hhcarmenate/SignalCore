<?php

namespace App\Data\MarketData;

readonly class QuoteData
{
    public function __construct(
        public string $symbol,
        public float $price,
        public float $change,
        public float $changePercent,
        public float $open,
        public float $high,
        public float $low,
        public float $previousClose,
        public int $volume,
        public string $currency,
        public string $asOf,
    ) {
    }

    public function toArray(): array
    {
        return [
            'symbol' => $this->symbol,
            'price' => $this->price,
            'change' => $this->change,
            'change_percent' => $this->changePercent,
            'open' => $this->open,
            'high' => $this->high,
            'low' => $this->low,
            'previous_close' => $this->previousClose,
            'volume' => $this->volume,
            'currency' => $this->currency,
            'as_of' => $this->asOf,
        ];
    }
}
