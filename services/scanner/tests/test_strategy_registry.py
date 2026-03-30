import unittest

from signalcore_scanner.strategies.registry import DEFAULT_STRATEGIES, EXCLUDED_FROM_MVP, MVP_STRATEGIES


class StrategyRegistryTest(unittest.TestCase):
    def test_mvp_strategy_set_has_expected_priority_order(self) -> None:
        self.assertEqual(
            ['trend_continuation', 'breakout_confirmation', 'mean_reversion_to_trend'],
            [strategy.key for strategy in MVP_STRATEGIES],
        )
        self.assertEqual([1, 2, 3], [strategy.priority for strategy in MVP_STRATEGIES])

    def test_mvp_strategies_support_bullish_and_bearish_directional_mapping(self) -> None:
        for strategy in MVP_STRATEGIES:
            self.assertEqual(('bullish', 'bearish'), strategy.directional_biases)
            self.assertEqual(('call', 'put'), strategy.execution_hints)
            self.assertTrue(strategy.included_in_mvp)
            self.assertTrue(strategy.enabled_by_default)

    def test_registry_and_mvp_exclusions_are_explicit(self) -> None:
        self.assertEqual(set(DEFAULT_STRATEGIES.keys()), {'trend_continuation', 'breakout_confirmation', 'mean_reversion_to_trend'})
        self.assertIn('counter_trend_reversal', EXCLUDED_FROM_MVP)
        self.assertIn('range_rotation', EXCLUDED_FROM_MVP)
        self.assertIn('volatility_expansion_scalp', EXCLUDED_FROM_MVP)


if __name__ == '__main__':
    unittest.main()
