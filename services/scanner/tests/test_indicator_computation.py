import unittest

from signalcore_scanner.contracts.candle_point import CandlePoint
from signalcore_scanner.indicators import (
    IndicatorCalculator,
    average_true_range,
    exponential_moving_average,
    relative_strength_index,
    simple_moving_average,
)


class IndicatorComputationTest(unittest.TestCase):
    def test_simple_moving_average_returns_expected_value(self) -> None:
        self.assertEqual(4.0, simple_moving_average([1, 2, 3, 4, 5], 3))

    def test_exponential_moving_average_returns_expected_value(self) -> None:
        self.assertEqual(4.0, exponential_moving_average([1, 2, 3, 4, 5], 3))

    def test_relative_strength_index_returns_100_when_there_are_no_losses(self) -> None:
        self.assertEqual(100.0, relative_strength_index([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15], 14))

    def test_average_true_range_returns_expected_value(self) -> None:
        highs = [10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24]
        lows = [9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23]
        closes = [9.5, 10.5, 11.5, 12.5, 13.5, 14.5, 15.5, 16.5, 17.5, 18.5, 19.5, 20.5, 21.5, 22.5, 23.5]

        self.assertEqual(1.5, average_true_range(highs, lows, closes, 14))

    def test_indicator_calculator_builds_reusable_snapshot(self) -> None:
        candles = tuple(
            CandlePoint(
                symbol_id=1,
                symbol='SPY',
                timeframe='1d',
                bar_time=f'2026-03-{day:02d}T00:00:00+00:00',
                open=float(100 + day),
                high=float(101 + day),
                low=float(99 + day),
                close=float(100 + day),
                volume=1000000 + day,
                is_final=True,
            )
            for day in range(1, 61)
        )

        snapshot = IndicatorCalculator().build_snapshot(candles)
        payload = snapshot.to_payload()

        self.assertEqual(160.0, snapshot.close)
        self.assertIn('sma_20', payload)
        self.assertIn('sma_50', payload)
        self.assertIn('ema_20', payload)
        self.assertIn('ema_50', payload)
        self.assertIn('rsi_14', payload)
        self.assertIn('atr_14', payload)
        self.assertIn('volume_sma_20', payload)


if __name__ == '__main__':
    unittest.main()
