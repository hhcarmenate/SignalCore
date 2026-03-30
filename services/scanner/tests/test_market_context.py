import unittest

from signalcore_scanner.contracts.candle_point import CandlePoint
from signalcore_scanner.indicators.indicator_calculator import IndicatorCalculator
from signalcore_scanner.market_context import MarketContextAnalyzer
from signalcore_scanner.market_context.swing_levels import SwingLevelDetector
from signalcore_scanner.market_context.trend_bias import TrendBiasAnalyzer
from signalcore_scanner.market_context.volume_context import VolumeContextAnalyzer


class MarketContextTest(unittest.TestCase):
    def _candles(self) -> tuple[CandlePoint, ...]:
        return tuple(
            CandlePoint(
                symbol_id=1,
                symbol='SPY',
                timeframe='1d',
                bar_time=f'2026-03-{day:02d}T00:00:00+00:00',
                open=float(100 + day),
                high=float(101 + day),
                low=float(99 + day),
                close=float(100 + day),
                volume=1000000 + (day * 1000),
                is_final=True,
            )
            for day in range(1, 61)
        )

    def test_trend_bias_analyzer_detects_bullish_bias(self) -> None:
        candles = self._candles()
        indicators = IndicatorCalculator().build_snapshot(candles)

        bias = TrendBiasAnalyzer().detect(candles, indicators)

        self.assertEqual('bullish', bias)

    def test_swing_level_detector_returns_recent_extremes(self) -> None:
        swing_high, swing_low = SwingLevelDetector().detect(self._candles(), window=10)

        self.assertEqual(161.0, swing_high)
        self.assertEqual(150.0, swing_low)

    def test_volume_context_analyzer_detects_expanding_volume(self) -> None:
        candles = self._candles()
        indicators = IndicatorCalculator().build_snapshot(candles)

        state = VolumeContextAnalyzer().detect(candles, indicators)

        self.assertEqual('normal', state)

    def test_market_context_analyzer_builds_reusable_snapshot(self) -> None:
        candles = self._candles()
        indicators = IndicatorCalculator().build_snapshot(candles)

        result = MarketContextAnalyzer().build(candles, indicators)

        self.assertEqual('bullish', result.context.trend_bias)
        self.assertEqual('bullish', result.context.higher_timeframe_bias)
        self.assertEqual('trend', result.context.regime)
        self.assertEqual('normal', result.context.volume_state)
        self.assertEqual(161.0, result.context.swing_levels.swing_high)
        self.assertEqual(140.0, result.context.swing_levels.breakdown_level)
        self.assertEqual(tuple(), result.context.notes)


if __name__ == '__main__':
    unittest.main()
