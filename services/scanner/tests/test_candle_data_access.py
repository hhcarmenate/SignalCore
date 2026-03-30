import unittest

from signalcore_scanner.contracts.candle_point import CandlePoint
from signalcore_scanner.contracts.candle_query import CandleQuery
from signalcore_scanner.contracts.watchlist_symbol import WatchlistSymbol
from signalcore_scanner.data_access.candle_access_planner import CandleAccessPlanner
from signalcore_scanner.data_access.candle_repository import InMemoryCandleRepository
from signalcore_scanner.data_access.postgres_candle_query_builder import PostgresCandleQueryBuilder
from signalcore_scanner.data_access.watchlist_symbol_repository import InMemoryWatchlistSymbolRepository


class CandleDataAccessTest(unittest.TestCase):
    def test_candle_access_planner_uses_watchlist_symbol_universe(self) -> None:
        planner = CandleAccessPlanner(
            InMemoryWatchlistSymbolRepository(
                symbols_by_watchlist={
                    11: [
                        WatchlistSymbol(symbol_id=1, symbol='SPY', asset_type='etf', market='us_equities'),
                        WatchlistSymbol(symbol_id=2, symbol='QQQ', asset_type='etf', market='us_equities'),
                    ]
                }
            )
        )

        plan = planner.build_plan(CandleQuery(watchlist_id=11, timeframe='4h', lookback=3))

        self.assertEqual(11, plan.query.watchlist_id)
        self.assertEqual('4h', plan.query.timeframe)
        self.assertEqual([1, 2], [symbol.symbol_id for symbol in plan.symbols])

    def test_in_memory_candle_repository_returns_latest_lookback_per_symbol(self) -> None:
        repository = InMemoryCandleRepository(
            candles=[
                CandlePoint(1, 'SPY', '4h', '2026-03-30T08:00:00+00:00', 1, 2, 0.5, 1.5, 100, True),
                CandlePoint(1, 'SPY', '4h', '2026-03-30T12:00:00+00:00', 2, 3, 1.5, 2.5, 110, True),
                CandlePoint(1, 'SPY', '4h', '2026-03-30T16:00:00+00:00', 3, 4, 2.5, 3.5, 120, True),
                CandlePoint(2, 'QQQ', '4h', '2026-03-30T08:00:00+00:00', 10, 11, 9, 10.5, 200, True),
                CandlePoint(2, 'QQQ', '4h', '2026-03-30T12:00:00+00:00', 11, 12, 10, 11.5, 210, False),
                CandlePoint(2, 'QQQ', '4h', '2026-03-30T16:00:00+00:00', 12, 13, 11, 12.5, 220, True),
            ]
        )

        candles = repository.get_candles(
            CandleQuery(watchlist_id=11, timeframe='4h', lookback=2, only_final=True),
            symbol_ids=[1, 2],
        )

        self.assertEqual(['2026-03-30T12:00:00+00:00', '2026-03-30T16:00:00+00:00'], [c.bar_time for c in candles[1]])
        self.assertEqual(['2026-03-30T08:00:00+00:00', '2026-03-30T16:00:00+00:00'], [c.bar_time for c in candles[2]])

    def test_postgres_query_builder_uses_windowed_lookback_pattern(self) -> None:
        spec = PostgresCandleQueryBuilder().build(
            CandleQuery(watchlist_id=11, timeframe='1d', lookback=50, only_final=True),
            symbol_ids=[1, 2, 3],
        )

        self.assertIn('ROW_NUMBER() OVER', spec.sql)
        self.assertIn('PARTITION BY c.symbol_id, c.timeframe', spec.sql)
        self.assertIn('WHERE row_number <= :lookback', spec.sql)
        self.assertEqual([1, 2, 3], spec.bindings['symbol_ids'])
        self.assertEqual('1d', spec.bindings['timeframe'])
        self.assertEqual(50, spec.bindings['lookback'])
        self.assertTrue(spec.bindings['only_final'])


if __name__ == '__main__':
    unittest.main()
