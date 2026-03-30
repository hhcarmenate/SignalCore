from dataclasses import dataclass

from signalcore_scanner.contracts.candle_query import CandleQuery
from signalcore_scanner.contracts.watchlist_symbol import WatchlistSymbol


@dataclass(frozen=True)
class CandleAccessPlan:
    query: CandleQuery
    symbols: list[WatchlistSymbol]
