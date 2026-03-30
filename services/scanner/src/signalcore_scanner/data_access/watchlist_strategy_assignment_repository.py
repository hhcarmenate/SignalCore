from dataclasses import dataclass
from typing import Protocol

from signalcore_scanner.contracts.watchlist_strategy_assignment import WatchlistStrategyAssignment


class WatchlistStrategyAssignmentRepository(Protocol):
    def get_assignments_for_watchlist(self, watchlist_id: int) -> list[WatchlistStrategyAssignment]: ...


@dataclass
class InMemoryWatchlistStrategyAssignmentRepository:
    assignments: list[WatchlistStrategyAssignment]

    def get_assignments_for_watchlist(self, watchlist_id: int) -> list[WatchlistStrategyAssignment]:
        return [assignment for assignment in self.assignments if assignment.watchlist_id == watchlist_id]
