import unittest

from signalcore_scanner.contracts.scanner_run_output import build_strategy_result
from signalcore_scanner.contracts.scanner_signal_output import ScannerSignalOutput
from signalcore_scanner.contracts.strategy_execution_input import StrategyExecutionInput
from signalcore_scanner.contracts.watchlist_symbol import WatchlistSymbol
from signalcore_scanner.runtime import ExecutionPlanner, RuntimePlan, ScannerExecutionEngine


class FakeStrategyExecutor:
    def __init__(self, fail_symbol: str | None = None) -> None:
        self.fail_symbol = fail_symbol

    def execute(self, execution_input: StrategyExecutionInput):
        if self.fail_symbol == execution_input.symbol.symbol:
            raise RuntimeError(f'boom:{execution_input.symbol.symbol}')

        signal = ScannerSignalOutput(
            strategy_key=execution_input.strategy_key,
            symbol=execution_input.symbol.symbol,
            timeframe=execution_input.timeframe,
            direction='bullish',
            thesis='Synthetic signal for framework testing.',
            confidence=0.5,
            score=50.0,
            signal_category='test',
        )

        return build_strategy_result(execution_input, (signal,), {'executor': 'fake'})


class ScannerExecutionFrameworkTest(unittest.TestCase):
    def test_execution_planner_batches_targets_by_batch_size(self) -> None:
        runtime_plan = RuntimePlan(
            watchlist_id=4,
            available_strategy_keys=['trend_continuation', 'breakout_confirmation'],
            globally_enabled_strategy_keys=['trend_continuation', 'breakout_confirmation'],
            assigned_strategy_keys=['trend_continuation', 'breakout_confirmation'],
            enabled_strategy_keys=['trend_continuation', 'breakout_confirmation'],
            timeframe='4h',
            batch_size=2,
        )
        symbols = [
            WatchlistSymbol(symbol_id=1, symbol='SPY', asset_type='etf', market='us_equities'),
            WatchlistSymbol(symbol_id=2, symbol='QQQ', asset_type='etf', market='us_equities'),
        ]

        plan = ExecutionPlanner().build(runtime_plan, symbols)

        self.assertEqual(2, len(plan.batches))
        self.assertEqual(2, len(plan.batches[0].targets))
        self.assertEqual(2, len(plan.batches[1].targets))

    def test_execution_engine_isolates_target_failures(self) -> None:
        runtime_plan = RuntimePlan(
            watchlist_id=4,
            available_strategy_keys=['trend_continuation'],
            globally_enabled_strategy_keys=['trend_continuation'],
            assigned_strategy_keys=['trend_continuation'],
            enabled_strategy_keys=['trend_continuation'],
            timeframe='1d',
            batch_size=10,
        )
        symbols = [
            WatchlistSymbol(symbol_id=1, symbol='SPY', asset_type='etf', market='us_equities'),
            WatchlistSymbol(symbol_id=2, symbol='QQQ', asset_type='etf', market='us_equities'),
        ]

        plan = ExecutionPlanner().build(runtime_plan, symbols)
        report = ScannerExecutionEngine(FakeStrategyExecutor(fail_symbol='QQQ')).execute(plan)

        self.assertEqual(1, len(report.strategy_results))
        self.assertEqual('SPY', report.strategy_results[0].symbol)
        self.assertEqual(1, len(report.failures))
        self.assertEqual('target_failed', report.failures[0].status)
        self.assertEqual('QQQ', report.failures[0].symbol)
        self.assertEqual('completed', report.lifecycle[-1].status)


if __name__ == '__main__':
    unittest.main()
