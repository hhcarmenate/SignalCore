from dataclasses import dataclass


@dataclass(frozen=True)
class CandleQuery:
    watchlist_id: int
    timeframe: str
    lookback: int
    only_final: bool = True
