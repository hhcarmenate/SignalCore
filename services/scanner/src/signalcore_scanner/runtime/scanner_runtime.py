from signalcore_scanner.data_access.strategy_state_repository import StrategyStateRepository
from signalcore_scanner.data_access.watchlist_strategy_assignment_repository import WatchlistStrategyAssignmentRepository
from signalcore_scanner.runtime.runtime_plan import RuntimePlan
from signalcore_scanner.strategies.registry import DEFAULT_STRATEGIES


class ScannerRuntime:
    def __init__(
        self,
        strategy_state_repository: StrategyStateRepository,
        watchlist_strategy_assignment_repository: WatchlistStrategyAssignmentRepository,
    ) -> None:
        self.strategy_state_repository = strategy_state_repository
        self.watchlist_strategy_assignment_repository = watchlist_strategy_assignment_repository

    def build_runtime_plan(self, watchlist_id: int, timeframe: str | None = None, batch_size: int = 50) -> RuntimePlan:
        available_strategy_keys = list(DEFAULT_STRATEGIES.keys())
        states = {state.strategy_key: state.is_enabled for state in self.strategy_state_repository.get_states()}
        globally_enabled_strategy_keys = [
            key for key in available_strategy_keys
            if states.get(key, DEFAULT_STRATEGIES[key].enabled_by_default)
        ]
        assigned_strategy_keys = [
            assignment.strategy_key
            for assignment in self.watchlist_strategy_assignment_repository.get_assignments_for_watchlist(watchlist_id)
            if assignment.strategy_key in DEFAULT_STRATEGIES
        ]
        enabled_strategy_keys = [
            key for key in assigned_strategy_keys
            if key in globally_enabled_strategy_keys
        ]

        return RuntimePlan(
            watchlist_id=watchlist_id,
            available_strategy_keys=available_strategy_keys,
            globally_enabled_strategy_keys=globally_enabled_strategy_keys,
            assigned_strategy_keys=assigned_strategy_keys,
            enabled_strategy_keys=enabled_strategy_keys,
            timeframe=timeframe,
            batch_size=batch_size,
        )
