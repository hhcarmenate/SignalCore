import unittest

from signalcore_scanner.contracts.strategy_state import StrategyState
from signalcore_scanner.contracts.watchlist_strategy_assignment import WatchlistStrategyAssignment
from signalcore_scanner.data_access.strategy_state_repository import InMemoryStrategyStateRepository
from signalcore_scanner.data_access.watchlist_strategy_assignment_repository import InMemoryWatchlistStrategyAssignmentRepository
from signalcore_scanner.runtime.scanner_runtime import ScannerRuntime


class RuntimePlanTest(unittest.TestCase):
    def test_runtime_plan_filters_global_state_by_watchlist_assignment(self) -> None:
        runtime = ScannerRuntime(
            InMemoryStrategyStateRepository(
                states=[
                    StrategyState(strategy_key="trend_continuation", is_enabled=True),
                    StrategyState(strategy_key="breakout_confirmation", is_enabled=False),
                    StrategyState(strategy_key="mean_reversion_to_trend", is_enabled=True),
                ]
            ),
            InMemoryWatchlistStrategyAssignmentRepository(
                assignments=[
                    WatchlistStrategyAssignment(watchlist_id=7, strategy_key="trend_continuation"),
                    WatchlistStrategyAssignment(watchlist_id=7, strategy_key="breakout_confirmation"),
                    WatchlistStrategyAssignment(watchlist_id=9, strategy_key="mean_reversion_to_trend"),
                ]
            ),
        )

        plan = runtime.build_runtime_plan(watchlist_id=7)

        self.assertEqual(7, plan.watchlist_id)
        self.assertEqual(
            [
                "trend_continuation",
                "breakout_confirmation",
                "mean_reversion_to_trend",
            ],
            plan.available_strategy_keys,
        )
        self.assertEqual(
            [
                "trend_continuation",
                "mean_reversion_to_trend",
            ],
            plan.globally_enabled_strategy_keys,
        )
        self.assertEqual(
            [
                "trend_continuation",
                "breakout_confirmation",
            ],
            plan.assigned_strategy_keys,
        )
        self.assertEqual(
            [
                "trend_continuation",
            ],
            plan.enabled_strategy_keys,
        )


if __name__ == "__main__":
    unittest.main()
