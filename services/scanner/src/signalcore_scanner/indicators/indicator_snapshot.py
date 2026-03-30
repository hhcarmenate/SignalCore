from dataclasses import dataclass


@dataclass(frozen=True)
class IndicatorSnapshot:
    close: float | None = None
    sma_20: float | None = None
    sma_50: float | None = None
    ema_20: float | None = None
    ema_50: float | None = None
    rsi_14: float | None = None
    atr_14: float | None = None
    volume_sma_20: float | None = None

    def to_payload(self) -> dict[str, float]:
        return {
            key: value
            for key, value in {
                'close': self.close,
                'sma_20': self.sma_20,
                'sma_50': self.sma_50,
                'ema_20': self.ema_20,
                'ema_50': self.ema_50,
                'rsi_14': self.rsi_14,
                'atr_14': self.atr_14,
                'volume_sma_20': self.volume_sma_20,
            }.items()
            if value is not None
        }
