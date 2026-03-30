from dataclasses import dataclass, field


@dataclass(frozen=True)
class TradeLevels:
    entry: float | None = None
    stop_loss: float | None = None
    target: float | None = None


@dataclass(frozen=True)
class SignalScoreBreakdown:
    trend_alignment: float = 0.0
    confidence: float = 0.0
    volume_confirmation: float = 0.0
    volatility_quality: float = 0.0
    structure_quality: float = 0.0
    composite: float = 0.0

    def to_payload(self) -> dict[str, float]:
        return {
            'trend_alignment': self.trend_alignment,
            'confidence': self.confidence,
            'volume_confirmation': self.volume_confirmation,
            'volatility_quality': self.volatility_quality,
            'structure_quality': self.structure_quality,
            'composite': self.composite,
        }


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
    ranking_score: float | None = None
    ranking_position: int | None = None
    score_breakdown: SignalScoreBreakdown = field(default_factory=SignalScoreBreakdown)
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
            'ranking_score': self.ranking_score,
            'ranking_position': self.ranking_position,
            'score_breakdown': self.score_breakdown.to_payload(),
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
