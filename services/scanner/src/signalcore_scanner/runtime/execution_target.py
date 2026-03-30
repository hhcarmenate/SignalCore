from dataclasses import dataclass

from signalcore_scanner.contracts.watchlist_symbol import WatchlistSymbol


@dataclass(frozen=True)
class ExecutionTarget:
    watchlist_id: int
    timeframe: str
    strategy_key: str
    symbol: WatchlistSymbol
