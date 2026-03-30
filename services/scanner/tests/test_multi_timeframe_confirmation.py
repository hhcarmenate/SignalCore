import unittest

from signalcore_scanner.confirmation import MultiTimeframeConfirmer
from signalcore_scanner.contracts.multi_timeframe_confirmation_input import MultiTimeframeConfirmationInput, TimeframeContext
from signalcore_scanner.contracts.strategy_execution_input import MarketContextSnapshot


class MultiTimeframeConfirmationTest(unittest.TestCase):
    def test_confirmer_accepts_aligned_higher_timeframe_setup(self) -> None:
        result = MultiTimeframeConfirmer().confirm(
            MultiTimeframeConfirmationInput(
                direction='bullish',
                trigger_timeframe='4h',
                higher_timeframe='1d',
                trigger_score=78.0,
                trigger_context=TimeframeContext(
                    timeframe='4h',
                    trend_bias='bullish',
                    market_context=MarketContextSnapshot(trend_bias='bullish'),
                ),
                higher_timeframe_context=TimeframeContext(
                    timeframe='1d',
                    trend_bias='bullish',
                    market_context=MarketContextSnapshot(trend_bias='bullish', higher_timeframe_bias='bullish'),
                ),
            )
        )

        self.assertTrue(result.is_confirmed)
        self.assertEqual(100.0, result.alignment_score)
        self.assertFalse(result.conflict_detected)
        self.assertTrue(result.passed_minimum_threshold)

    def test_confirmer_rejects_conflicting_higher_timeframe_setup(self) -> None:
        result = MultiTimeframeConfirmer().confirm(
            MultiTimeframeConfirmationInput(
                direction='bullish',
                trigger_timeframe='4h',
                higher_timeframe='1d',
                trigger_score=82.0,
                trigger_context=TimeframeContext(timeframe='4h', trend_bias='bullish'),
                higher_timeframe_context=TimeframeContext(timeframe='1d', trend_bias='bearish'),
            )
        )

        self.assertFalse(result.is_confirmed)
        self.assertEqual(0.0, result.alignment_score)
        self.assertTrue(result.conflict_detected)
        self.assertIn('timeframe_conflict_detected', result.notes)

    def test_confirmer_rejects_setup_below_minimum_quality_threshold(self) -> None:
        result = MultiTimeframeConfirmer().confirm(
            MultiTimeframeConfirmationInput(
                direction='bearish',
                trigger_timeframe='4h',
                higher_timeframe='1d',
                trigger_score=55.0,
                minimum_score_threshold=60.0,
                trigger_context=TimeframeContext(timeframe='4h', trend_bias='bearish'),
                higher_timeframe_context=TimeframeContext(timeframe='1d', trend_bias='bearish'),
            )
        )

        self.assertFalse(result.is_confirmed)
        self.assertTrue(result.passed_minimum_threshold is False)
        self.assertIn('below_minimum_quality_threshold', result.notes)


if __name__ == '__main__':
    unittest.main()
