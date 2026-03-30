from signalcore_scanner.contracts.candle_access_plan import CandleAccessPlan
from signalcore_scanner.contracts.candle_query import CandleQuery
from signalcore_scanner.data_access.watchlist_symbol_repository import WatchlistSymbolRepository


class CandleAccessPlanner:
    def __init__(self, watchlist_symbol_repository: WatchlistSymbolRepository) -> None:
        self.watchlist_symbol_repository = watchlist_symbol_repository

    def build_plan(self, query: CandleQuery) -> CandleAccessPlan:
        symbols = self.watchlist_symbol_repository.get_symbols_for_watchlist(query.watchlist_id)

        return CandleAccessPlan(query=query, symbols=symbols)
