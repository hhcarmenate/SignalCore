<?php

namespace App\Data\MarketData;

readonly class CandleData
{
    public function __construct(
        public string $symbol,
        public string $timeframe,
        public string $timestamp,
        public float $open,
        public float $high,
        public float $low,
        public float $close,
        public int $volume,
    ) {
    }

    public function toArray(): array
    {
        return [
            'symbol' => $this->symbol,
            'timeframe' => $this->timeframe,
            'timestamp' => $this->timestamp,
            'open' => $this->open,
            'high' => $this->high,
            'low' => $this->low,
            'close' => $this->close,
            'volume' => $this->volume,
        ];
    }
}
