from dataclasses import dataclass, field


@dataclass(frozen=True)
class TradeLevels:
    entry: float | None = None
    stop_loss: float | None = None
    target: float | None = None


@dataclass(frozen=True)
class ScannerSignalOutput:
    strategy_key: str
    symbol: str
    timeframe: str
    direction: str
    thesis: str
    confidence: float
    score: float
    signal_category: str
    execution_hint: str | None = None
    levels: TradeLevels = field(default_factory=TradeLevels)
    indicators: dict[str, float | int | str | bool] = field(default_factory=dict)
    context: dict[str, float | int | str | bool] = field(default_factory=dict)
    metadata: dict[str, float | int | str | bool] = field(default_factory=dict)

    def to_payload(self) -> dict[str, object]:
        return {
            'strategy_key': self.strategy_key,
            'symbol': self.symbol,
            'timeframe': self.timeframe,
            'direction': self.direction,
            'thesis': self.thesis,
            'confidence': self.confidence,
            'score': self.score,
            'signal_category': self.signal_category,
            'execution_hint': self.execution_hint,
            'levels': {
                'entry': self.levels.entry,
                'stop_loss': self.levels.stop_loss,
                'target': self.levels.target,
            },
            'indicators': self.indicators,
            'context': self.context,
            'metadata': self.metadata,
        }
