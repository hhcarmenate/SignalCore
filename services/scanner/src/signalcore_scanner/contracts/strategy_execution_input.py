from dataclasses import dataclass, field

from signalcore_scanner.contracts.candle_point import CandlePoint
from signalcore_scanner.contracts.watchlist_symbol import WatchlistSymbol


@dataclass(frozen=True)
class SwingLevels:
    swing_high: float | None = None
    swing_low: float | None = None
    breakout_level: float | None = None
    breakdown_level: float | None = None


@dataclass(frozen=True)
class MarketContextSnapshot:
    trend_bias: str | None = None
    higher_timeframe_bias: str | None = None
    regime: str | None = None
    volatility_state: str | None = None
    volume_state: str | None = None
    swing_levels: SwingLevels = field(default_factory=SwingLevels)
    notes: tuple[str, ...] = ()


@dataclass(frozen=True)
class StrategyExecutionInput:
    strategy_key: str
    watchlist_id: int
    symbol: WatchlistSymbol
    timeframe: str
    candles: tuple[CandlePoint, ...]
    market_context: MarketContextSnapshot = field(default_factory=MarketContextSnapshot)
    max_lookback: int = 0
    run_metadata: dict[str, str] = field(default_factory=dict)
