from collections import defaultdict
from dataclasses import dataclass
from typing import Protocol

from signalcore_scanner.contracts.candle_point import CandlePoint
from signalcore_scanner.contracts.candle_query import CandleQuery


class CandleRepository(Protocol):
    def get_candles(self, query: CandleQuery, symbol_ids: list[int]) -> dict[int, list[CandlePoint]]: ...


@dataclass
class InMemoryCandleRepository:
    candles: list[CandlePoint]

    def get_candles(self, query: CandleQuery, symbol_ids: list[int]) -> dict[int, list[CandlePoint]]:
        grouped: dict[int, list[CandlePoint]] = defaultdict(list)

        filtered = [
            candle for candle in self.candles
            if candle.symbol_id in symbol_ids
            and candle.timeframe == query.timeframe
            and (candle.is_final or not query.only_final)
        ]

        filtered.sort(key=lambda candle: (candle.symbol_id, candle.bar_time), reverse=True)

        per_symbol_counts: dict[int, int] = defaultdict(int)
        for candle in filtered:
            if per_symbol_counts[candle.symbol_id] >= query.lookback:
                continue

            grouped[candle.symbol_id].append(candle)
            per_symbol_counts[candle.symbol_id] += 1

        for symbol_id in list(grouped.keys()):
            grouped[symbol_id] = list(sorted(grouped[symbol_id], key=lambda candle: candle.bar_time))

        return dict(grouped)
