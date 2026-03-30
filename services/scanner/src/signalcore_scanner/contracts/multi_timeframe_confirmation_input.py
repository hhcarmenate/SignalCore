from dataclasses import dataclass, field

from signalcore_scanner.contracts.strategy_execution_input import MarketContextSnapshot


@dataclass(frozen=True)
class TimeframeContext:
    timeframe: str
    trend_bias: str | None = None
    market_context: MarketContextSnapshot = field(default_factory=MarketContextSnapshot)


@dataclass(frozen=True)
class MultiTimeframeConfirmationInput:
    direction: str
    trigger_timeframe: str
    higher_timeframe: str
    trigger_score: float
    minimum_score_threshold: float = 60.0
    trigger_context: TimeframeContext = field(default_factory=lambda: TimeframeContext(timeframe='4h'))
    higher_timeframe_context: TimeframeContext = field(default_factory=lambda: TimeframeContext(timeframe='1d'))
