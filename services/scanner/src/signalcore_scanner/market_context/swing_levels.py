from signalcore_scanner.contracts.candle_point import CandlePoint


class SwingLevelDetector:
    def detect(self, candles: tuple[CandlePoint, ...], window: int = 20) -> tuple[float | None, float | None]:
        if not candles:
            return None, None

        subset = candles[-window:]
        swing_high = max(float(candle.high) for candle in subset)
        swing_low = min(float(candle.low) for candle in subset)

        return round(swing_high, 4), round(swing_low, 4)
