from dataclasses import dataclass


@dataclass(frozen=True)
class CandlePoint:
    symbol_id: int
    symbol: str
    timeframe: str
    bar_time: str
    open: float
    high: float
    low: float
    close: float
    volume: int
    is_final: bool
