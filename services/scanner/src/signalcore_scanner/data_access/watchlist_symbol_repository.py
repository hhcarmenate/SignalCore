from dataclasses import dataclass
from typing import Protocol

from signalcore_scanner.contracts.watchlist_symbol import WatchlistSymbol


class WatchlistSymbolRepository(Protocol):
    def get_symbols_for_watchlist(self, watchlist_id: int) -> list[WatchlistSymbol]: ...


@dataclass
class InMemoryWatchlistSymbolRepository:
    symbols_by_watchlist: dict[int, list[WatchlistSymbol]]

    def get_symbols_for_watchlist(self, watchlist_id: int) -> list[WatchlistSymbol]:
        return self.symbols_by_watchlist.get(watchlist_id, [])
