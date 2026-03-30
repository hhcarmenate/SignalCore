from dataclasses import dataclass


@dataclass(frozen=True)
class WatchlistSymbol:
    symbol_id: int
    symbol: str
    asset_type: str
    market: str
