from dataclasses import dataclass


@dataclass(frozen=True)
class WatchlistStrategyAssignment:
    watchlist_id: int
    strategy_key: str
