from signalcore_scanner.contracts.candle_point import CandlePoint
from signalcore_scanner.indicators.atr import average_true_range
from signalcore_scanner.indicators.exponential_moving_average import exponential_moving_average
from signalcore_scanner.indicators.indicator_snapshot import IndicatorSnapshot
from signalcore_scanner.indicators.moving_averages import simple_moving_average
from signalcore_scanner.indicators.rsi import relative_strength_index


class IndicatorCalculator:
    def build_snapshot(self, candles: tuple[CandlePoint, ...]) -> IndicatorSnapshot:
        if not candles:
            return IndicatorSnapshot()

        closes = [float(candle.close) for candle in candles]
        highs = [float(candle.high) for candle in candles]
        lows = [float(candle.low) for candle in candles]
        volumes = [float(candle.volume) for candle in candles]

        return IndicatorSnapshot(
            close=round(closes[-1], 4),
            sma_20=simple_moving_average(closes, 20),
            sma_50=simple_moving_average(closes, 50),
            ema_20=exponential_moving_average(closes, 20),
            ema_50=exponential_moving_average(closes, 50),
            rsi_14=relative_strength_index(closes, 14),
            atr_14=average_true_range(highs, lows, closes, 14),
            volume_sma_20=simple_moving_average(volumes, 20),
        )
