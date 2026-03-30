from signalcore_scanner.contracts.strategy_state import StrategyState
from signalcore_scanner.contracts.watchlist_strategy_assignment import WatchlistStrategyAssignment
from signalcore_scanner.data_access.strategy_state_repository import InMemoryStrategyStateRepository
from signalcore_scanner.data_access.watchlist_strategy_assignment_repository import InMemoryWatchlistStrategyAssignmentRepository
from signalcore_scanner.runtime.scanner_runtime import ScannerRuntime


if __name__ == "__main__":
    runtime = ScannerRuntime(
        InMemoryStrategyStateRepository(
            states=[
                StrategyState(strategy_key="trend_continuation", is_enabled=True),
                StrategyState(strategy_key="breakout_confirmation", is_enabled=True),
                StrategyState(strategy_key="mean_reversion_to_trend", is_enabled=True),
            ]
        ),
        InMemoryWatchlistStrategyAssignmentRepository(
            assignments=[
                WatchlistStrategyAssignment(watchlist_id=1, strategy_key="trend_continuation"),
                WatchlistStrategyAssignment(watchlist_id=1, strategy_key="breakout_confirmation"),
            ]
        ),
    )

    plan = runtime.build_runtime_plan(watchlist_id=1)

    print({
        "watchlist_id": plan.watchlist_id,
        "available_strategy_keys": plan.available_strategy_keys,
        "globally_enabled_strategy_keys": plan.globally_enabled_strategy_keys,
        "assigned_strategy_keys": plan.assigned_strategy_keys,
        "enabled_strategy_keys": plan.enabled_strategy_keys,
    })
