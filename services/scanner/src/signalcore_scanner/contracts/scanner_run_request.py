from dataclasses import dataclass


@dataclass(frozen=True)
class ScannerRunRequest:
    watchlist_id: int
    timeframe: str
    limit: int = 300
