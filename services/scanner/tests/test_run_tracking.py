import unittest

from signalcore_scanner.contracts.scanner_run_output import build_strategy_result
from signalcore_scanner.contracts.scanner_signal_output import ScannerSignalOutput
from signalcore_scanner.contracts.strategy_execution_input import StrategyExecutionInput
from signalcore_scanner.contracts.watchlist_symbol import WatchlistSymbol
from signalcore_scanner.runtime import ExecutionPlanner, RuntimePlan, ScannerExecutionEngine, ScannerRunTracker


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
            thesis='Synthetic signal for run tracking testing.',
            confidence=0.55,
            score=55.0,
            signal_category='test',
        )

        return build_strategy_result(execution_input, (signal,), {'executor': 'fake'})


class ScannerRunTrackingTest(unittest.TestCase):
    def test_tracker_builds_completed_with_errors_record(self) -> None:
        runtime_plan = RuntimePlan(
            watchlist_id=8,
            available_strategy_keys=['trend_continuation'],
            globally_enabled_strategy_keys=['trend_continuation'],
            assigned_strategy_keys=['trend_continuation'],
            enabled_strategy_keys=['trend_continuation'],
            timeframe='4h',
            batch_size=10,
        )
        symbols = [
            WatchlistSymbol(symbol_id=1, symbol='SPY', asset_type='etf', market='us_equities'),
            WatchlistSymbol(symbol_id=2, symbol='QQQ', asset_type='etf', market='us_equities'),
        ]

        plan = ExecutionPlanner().build(runtime_plan, symbols)
        report = ScannerExecutionEngine(FakeStrategyExecutor(fail_symbol='QQQ')).execute(plan)
        record = ScannerRunTracker().build_record(report, '2026-03-30T19:00:00+00:00', '2026-03-30T19:01:00+00:00')
        summary = ScannerRunTracker().summarize(record)

        self.assertEqual('completed_with_errors', record.status)
        self.assertEqual(1, record.symbols_scanned_count)
        self.assertEqual(1, record.strategies_executed_count)
        self.assertEqual(1, record.signals_found_count)
        self.assertEqual(1, record.error_count)
        self.assertEqual(1, len(record.errors))
        self.assertEqual('QQQ', record.errors[0].symbol)
        self.assertEqual('completed_with_errors', summary.status)

    def test_tracker_builds_failed_record_when_all_targets_fail(self) -> None:
        runtime_plan = RuntimePlan(
            watchlist_id=9,
            available_strategy_keys=['trend_continuation'],
            globally_enabled_strategy_keys=['trend_continuation'],
            assigned_strategy_keys=['trend_continuation'],
            enabled_strategy_keys=['trend_continuation'],
            timeframe='1d',
            batch_size=10,
        )
        symbols = [
            WatchlistSymbol(symbol_id=1, symbol='SPY', asset_type='etf', market='us_equities'),
        ]

        plan = ExecutionPlanner().build(runtime_plan, symbols)
        report = ScannerExecutionEngine(FakeStrategyExecutor(fail_symbol='SPY')).execute(plan)
        record = ScannerRunTracker().build_record(report, '2026-03-30T19:10:00+00:00', '2026-03-30T19:11:00+00:00')

        self.assertEqual('failed', record.status)
        self.assertEqual(0, record.symbols_scanned_count)
        self.assertEqual(0, record.strategies_executed_count)
        self.assertEqual(0, record.signals_found_count)
        self.assertEqual(1, record.error_count)


if __name__ == '__main__':
    unittest.main()
